<?php

namespace App\Jobs\Sla;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;
use App\Models\SlaPolicy;
use App\Models\AppliedSla;
use App\Jobs\Webhooks\SendWebhooksJob;
use App\Models\Inbox;
use Carbon\Carbon;

class CheckSlaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 120;

    public int $timeout = 180;

    public string $queue = 'sla';

    public function __construct(public int $conversationId)
    {
    }

    public function handle(): void
    {
        try {
            $conv = Conversation::find($this->conversationId);
            if (! $conv) {
                return;
            }

            // Evaluate active SLA policies for this account
            $policies = SlaPolicy::where('account_id', $conv->account_id)->active()->get();

            foreach ($policies as $policy) {
                $data = ['account_id' => $conv->account_id, 'sla_policy_id' => $policy->id, 'conversation_id' => $conv->id];

                // compute deadlines if thresholds set
                $firstAt = null;
                $nextAt = null;
                $resolutionAt = null;

                // If policy requires business-hours-only deadlines, compute with inbox working hours
                $inbox = Inbox::find($conv->inbox_id);

                if ($policy->first_response_time_threshold) {
                    if ($policy->only_during_business_hours && $inbox) {
                        $firstAt = $this->addBusinessSeconds($conv->created_at, (int) $policy->first_response_time_threshold, $inbox);
                    } else {
                        $firstAt = Carbon::parse($conv->created_at)->addSeconds((int) $policy->first_response_time_threshold);
                    }
                    $data['sla_first_response_at'] = $firstAt;
                }

                if ($policy->next_response_time_threshold) {
                    $base = $conv->last_activity_at ?? now();
                    if ($policy->only_during_business_hours && $inbox) {
                        $nextAt = $this->addBusinessSeconds($base, (int) $policy->next_response_time_threshold, $inbox);
                    } else {
                        $nextAt = Carbon::parse($base)->addSeconds((int) $policy->next_response_time_threshold);
                    }
                    $data['sla_next_response_at'] = $nextAt;
                }

                if ($policy->resolution_time_threshold) {
                    if ($policy->only_during_business_hours && $inbox) {
                        $resolutionAt = $this->addBusinessSeconds($conv->created_at, (int) $policy->resolution_time_threshold, $inbox);
                    } else {
                        $resolutionAt = Carbon::parse($conv->created_at)->addSeconds((int) $policy->resolution_time_threshold);
                    }
                    $data['sla_resolution_at'] = $resolutionAt;
                }

                AppliedSla::updateOrCreate(
                    ['conversation_id' => $conv->id, 'sla_policy_id' => $policy->id],
                    $data
                );

                // Check for immediate breaches (e.g., first response already late)
                $breaches = $policy->isBreached($conv);
                if (! empty($breaches)) {
                    Log::warning('SLA breached', ['conversation_id' => $conv->id, 'policy_id' => $policy->id, 'breaches' => $breaches]);

                    // emit webhook for SLA breach
                    SendWebhooksJob::dispatch($conv->account_id, 'sla_breached', ['conversation_id' => $conv->id, 'policy_id' => $policy->id, 'breaches' => $breaches]);
                }
            }

            Log::info('CheckSlaJob completed', ['conversation_id' => $this->conversationId]);
        } catch (\Throwable $e) {
            Log::error('CheckSlaJob failed', ['conversation_id' => $this->conversationId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Add seconds respecting inbox working hours (business hours).
     * $start may be a string or Carbon; returns Carbon in UTC.
     */
    private function addBusinessSeconds($start, int $seconds, Inbox $inbox): Carbon
    {
        $tz = $inbox->timezone ?? $inbox->account?->timezone ?? config('app.timezone', 'UTC');
        $cursor = Carbon::parse($start)->setTimezone($tz)->copy();

        // If working hours disabled on inbox, fallback to simple addition
        if (! $inbox->working_hours_enabled) {
            return $cursor->addSeconds($seconds)->setTimezone('UTC');
        }

        // Preload working hours keyed by day_of_week
        $workingHours = $inbox->workingHours()->get()->keyBy('day_of_week');

        while ($seconds > 0) {
            $dow = $cursor->dayOfWeek;
            $wh = $workingHours->get($dow);

            if (! $wh || $wh->closed_all_day) {
                // advance to next day's start
                $cursor = $this->nextOpenStart($cursor->copy()->addDay()->startOfDay(), $workingHours, $tz);
                continue;
            }

            $open = Carbon::createFromTime($wh->open_hour, $wh->open_minutes, 0, $tz)->setDate($cursor->year, $cursor->month, $cursor->day);
            $close = Carbon::createFromTime($wh->close_hour, $wh->close_minutes, 0, $tz)->setDate($cursor->year, $cursor->month, $cursor->day);

            if ($cursor->lessThan($open)) {
                $cursor = $open->copy();
            }

            if ($cursor->greaterThanOrEqualTo($close)) {
                $cursor = $this->nextOpenStart($cursor->copy()->addDay()->startOfDay(), $workingHours, $tz);
                continue;
            }

            $available = $close->getTimestamp() - $cursor->getTimestamp();
            if ($available >= $seconds) {
                $cursor = $cursor->addSeconds($seconds);
                $seconds = 0;
                break;
            }

            // consume today's remaining and move to next open day
            $seconds -= $available;
            $cursor = $this->nextOpenStart($cursor->copy()->addDay()->startOfDay(), $workingHours, $tz);
        }

        return $cursor->setTimezone('UTC');
    }

    /**
     * Find next open start datetime given a cursor date. Searches forward until open day found.
     */
    private function nextOpenStart(Carbon $cursorDayStart, $workingHours, string $tz): Carbon
    {
        for ($i = 0; $i < 30; $i++) {
            $dow = $cursorDayStart->dayOfWeek;
            $wh = $workingHours->get($dow);
            if ($wh && ! $wh->closed_all_day) {
                // build open datetime on that day
                return Carbon::createFromTime($wh->open_hour, $wh->open_minutes, 0, $tz)
                    ->setDate($cursorDayStart->year, $cursorDayStart->month, $cursorDayStart->day);
            }

            $cursorDayStart = $cursorDayStart->addDay();
        }

        // fallback to next day at midnight if nothing found
        return $cursorDayStart->setTimezone($tz)->startOfDay();
    }
}
