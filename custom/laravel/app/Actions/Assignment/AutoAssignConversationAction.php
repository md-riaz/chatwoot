<?php

namespace App\Actions\Assignment;

use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use App\Models\InboxAssignmentPolicy;
use App\Events\Conversation\ConversationAssigned;
use App\Repositories\Conversation\ConversationRepository;
use App\Repositories\Inbox\InboxRepository;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AutoAssignConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository,
        private InboxRepository $inboxRepository
    ) {}

    public function handle(int $conversationId): ?User
    {
        $conversation = $this->conversationRepository->find($conversationId);

        if (! $conversation || $conversation->assignee_id) {
            return null;
        }

        $inbox = $this->inboxRepository->find($conversation->inbox_id);

        if (! $inbox || ! $inbox->enable_auto_assignment) {
            return null;
        }

        $agent = $this->findBestAgent($inbox);

        if ($agent) {
            $previousAssignee = $conversation->assignee ?? null;

            $this->conversationRepository->update($conversation->id, [
                'assignee_id' => $agent->id,
            ]);

            $conversation = $this->conversationRepository->find($conversation->id);

            event(new ConversationAssigned($conversation, $agent, $previousAssignee));
        }

        return $agent;
    }

    private function findBestAgent(Inbox $inbox): ?User
    {
        // Get agents assigned to this inbox who are online
        $inboxMembers = $inbox->members()->where('availability', 1)->get();

        if ($inboxMembers->isEmpty()) {
            // Fallback to account-level users
            return $inbox->account
                ->users()
                ->wherePivot('availability', 1)
                ->inRandomOrder()
                ->first();
        }

        // Check if inbox has an assignment policy configured
        $inboxPolicy = InboxAssignmentPolicy::where('inbox_id', $inbox->id)
            ->with('assignmentPolicy')
            ->first();

            $policy = $inboxPolicy && $inboxPolicy->assignmentPolicy ? $inboxPolicy->assignmentPolicy : null;

        // Apply fair distribution constraints if configured
            $fairWindow = $policy ? $policy->fair_distribution_window : null;
            $fairLimit = $policy ? $policy->fair_distribution_limit : null;

        $candidateAgents = $inboxMembers;

        // Build open counts mapping for load evaluation
        $openCounts = $this->conversationRepository->countOpenByAssignee($inbox->account_id);

        // If policy exists and indicates round-robin (0), try to pick the next agent in rotation
        if ($policy && (int) $policy->assignment_order === 0) {
            // Determine last assignment times per agent for this inbox
            $lastAssigned = DB::table('conversations')
                ->where('inbox_id', $inbox->id)
                ->whereNotNull('assignee_id')
                ->select('assignee_id', DB::raw('MAX(updated_at) as last_assigned'))
                ->groupBy('assignee_id')
                ->orderBy('last_assigned', 'asc')
                ->pluck('last_assigned', 'assignee_id')
                ->toArray();

            // Sort inbox members by their last assignment (oldest first), missing entries come first
            $sorted = $candidateAgents->sortBy(function ($agent) use ($lastAssigned) {
                return $lastAssigned[$agent->id] ?? null;
            });

            // Apply fair distribution filter if configured: prefer agents under recent assignment limit
            if ($fairWindow && $fairLimit) {
                $since = now()->subSeconds((int) $fairWindow);
                $recentCounts = DB::table('conversations')
                    ->where('inbox_id', $inbox->id)
                    ->where('updated_at', '>=', $since)
                    ->whereNotNull('assignee_id')
                    ->select('assignee_id', DB::raw('count(*) as cnt'))
                    ->groupBy('assignee_id')
                    ->pluck('cnt', 'assignee_id')
                    ->toArray();

                $filtered = $sorted->filter(function ($agent) use ($recentCounts, $fairLimit) {
                    return ($recentCounts[$agent->id] ?? 0) < (int) $fairLimit;
                });

                if ($filtered->isNotEmpty()) {
                    return $filtered->first();
                }
            }

            return $sorted->first();
        }

        // Default: balanced assignment (least open conversations)
        $byLoad = $candidateAgents->sortBy(function ($agent) use ($openCounts) {
            return $openCounts[$agent->id] ?? 0;
        });

        // If fair distribution is configured, prefer agents below the recent assignment limit
        if ($policy && $fairWindow && $fairLimit) {
            $since = now()->subSeconds((int) $fairWindow);
            $recentCounts = DB::table('conversations')
                ->where('inbox_id', $inbox->id)
                ->where('updated_at', '>=', $since)
                ->whereNotNull('assignee_id')
                ->select('assignee_id', DB::raw('count(*) as cnt'))
                ->groupBy('assignee_id')
                ->pluck('cnt', 'assignee_id')
                ->toArray();

            $filtered = $byLoad->filter(function ($agent) use ($recentCounts, $fairLimit) {
                return ($recentCounts[$agent->id] ?? 0) < (int) $fairLimit;
            });

            if ($filtered->isNotEmpty()) {
                return $filtered->first();
            }
        }

        return $byLoad->first();
    }

    /**
     * Run as listener for ConversationCreated event.
     */
    public function asListener($event): void
    {
        $this->handle($event->conversation->id);
    }
}
