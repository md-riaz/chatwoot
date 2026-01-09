<?php

namespace App\Services;

use App\DataTransferObjects\SeedData;
use App\Enums\AccountUserRole;
use App\Models\Account;
use App\Models\User;
use App\Models\Team;
use App\Models\CustomRole;
use App\Models\Label;
use App\Models\Contact;
use App\Models\Inbox;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\CannedResponse;
use App\Models\AccountUser;
use App\Models\ContactInbox;
use App\Models\Channels\WebWidget;
use App\Models\Channels\FacebookPage;
use App\Models\Channels\TwitterProfile;
use App\Models\Channels\Whatsapp;
use App\Models\Channels\Sms;
use App\Models\Channels\Email;
use App\Models\Channels\Api;
use App\Models\Channels\Telegram;
use App\Models\Channels\Line;
use App\Models\Channels\Voice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeederService
{
    private Account $account;
    private SeedData $seedData;

    public function __construct(Account $account, ?SeedData $seedData = null)
    {

        $this->account = $account;
        $this->seedData = $seedData ?? SeedData::getDefault();
    }

    /**
     * Perform account seeding using Laravel factories and DTOs.
     */
    public function perform(): array
    {
        $stats = [
            'teams_created' => 0,
            'custom_roles_created' => 0,
            'users_created' => 0,
            'labels_created' => 0,
            'inboxes_created' => 0,
            'contacts_created' => 0,
            'conversations_created' => 0,
            'messages_created' => 0,
            'canned_responses_created' => 0,
        ];

        $this->cleanupExistingData();
        
        $stats['teams_created'] = $this->seedTeams();
        $stats['custom_roles_created'] = $this->seedCustomRoles();
        $stats['users_created'] = $this->seedUsers();
        $stats['labels_created'] = $this->seedLabels();
        $stats['inboxes_created'] = $this->seedInboxes();
        $stats['contacts_created'] = $this->seedContacts();
        $stats['canned_responses_created'] = $this->seedCannedResponses();

        return $stats;
    }

    /**
     * Clean up existing data.
     */
    private function cleanupExistingData(): void
    {
        // Use raw database queries to avoid model issues
        \Illuminate\Support\Facades\DB::table('team_members')
            ->whereIn('team_id', function($query) {
                $query->select('id')->from('teams')->where('account_id', $this->account->id);
            })->delete();
        
        // Delete account_users relationships first
        \Illuminate\Support\Facades\DB::table('account_users')->where('account_id', $this->account->id)->delete();
        
        // Delete test users (users with @paperlayer.test emails)
        \Illuminate\Support\Facades\DB::table('users')->where('email', 'like', '%@paperlayer.test')->delete();
        
        // Delete all channel tables for this account
        \Illuminate\Support\Facades\DB::table('channel_web_widgets')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_facebook_pages')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_twitter_profiles')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_whatsapp')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_sms')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_email')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_api')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_telegram')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_line')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('channel_voice')->where('account_id', $this->account->id)->delete();
        
        \Illuminate\Support\Facades\DB::table('teams')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('conversations')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('labels')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('labelings')->whereIn('label_id', function($query) {
            $query->select('id')->from('labels')->where('account_id', $this->account->id);
        })->delete();
        \Illuminate\Support\Facades\DB::table('inboxes')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('contacts')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('custom_roles')->where('account_id', $this->account->id)->delete();
        \Illuminate\Support\Facades\DB::table('canned_responses')->where('account_id', $this->account->id)->delete();
    }

    /**
     * Seed teams using Laravel factories.
     */
    private function seedTeams(): int
    {
        $count = 0;
        foreach ($this->seedData->teams as $teamName) {
            Team::factory()->create([
                'account_id' => $this->account->id,
                'name' => $teamName,
            ]);
            $count++;
        }
        return $count;
    }

    /**
     * Seed custom roles using Laravel factories.
     */
    private function seedCustomRoles(): int
    {
        $count = 0;
        foreach ($this->seedData->customRoles as $roleData) {
            CustomRole::factory()->create([
                'account_id' => $this->account->id,
                'name' => $roleData['name'],
                'description' => $roleData['description'],
                'permissions' => $roleData['permissions'],
            ]);
            $count++;
        }
        return $count;
    }

    /**
     * Seed users using Laravel factories.
     */
    private function seedUsers(): int
    {
        $count = 0;
        foreach ($this->seedData->users as $userData) {
            $user = User::factory()->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('Password1!.'),
                'email_verified_at' => now(),
            ]);

            $this->createAccountUser($user, $userData);
            
            if (!empty($userData['teams'])) {
                $this->addUserToTeams($user, $userData['teams']);
            }
            
            $count++;
        }
        return $count;
    }

    /**
     * Seed labels using Laravel factories.
     */
    private function seedLabels(): int
    {
        $count = 0;
        foreach ($this->seedData->labels as $labelData) {
            Label::factory()->create([
                'account_id' => $this->account->id,
                'title' => $labelData['title'],
                'color' => $labelData['color'],
                'show_on_sidebar' => $labelData['show_on_sidebar'],
            ]);
            $count++;
        }
        return $count;
    }

    /**
     * Seed inboxes using Laravel factories.
     */
    private function seedInboxes(): int
    {
        $companyData = $this->seedData->company;
        $count = 0;

        // Create all channel types using factories
        $channels = [
            'WebWidget' => fn() => WebWidget::factory()->create([
                'account_id' => $this->account->id,
                'website_url' => "https://{$companyData->domain}",
            ]),
            'FacebookPage' => fn() => FacebookPage::factory()->create([
                'account_id' => $this->account->id,
            ]),
            'TwitterProfile' => fn() => TwitterProfile::factory()->create([
                'account_id' => $this->account->id,
            ]),
            'Whatsapp' => fn() => Whatsapp::factory()->create([
                'account_id' => $this->account->id,
            ]),
            'Sms' => fn() => Sms::factory()->create([
                'account_id' => $this->account->id,
            ]),
            'Email' => fn() => Email::factory()->create([
                'account_id' => $this->account->id,
                'email' => "support-{$this->account->id}@{$companyData->domain}",
            ]),
            'Api' => fn() => Api::factory()->create([
                'account_id' => $this->account->id,
            ]),
            'Telegram' => fn() => Telegram::factory()->create([
                'account_id' => $this->account->id,
                'bot_name' => $companyData->name,
            ]),
            'Line' => fn() => Line::factory()->create([
                'account_id' => $this->account->id,
            ]),
            'Voice' => fn() => Voice::factory()->demo()->create([
                'account_id' => $this->account->id,
            ]),
        ];

        foreach ($channels as $channelType => $channelFactory) {
            $channel = $channelFactory();
            
            Inbox::factory()->create([
                'account_id' => $this->account->id,
                'name' => "{$companyData->name} {$channelType}",
                'channel_type' => $channel::class,
                'channel_id' => $channel->id,
            ]);
            
            $count++;
        }

        return $count;
    }

    /**
     * Seed contacts and conversations using Laravel factories.
     */
    private function seedContacts(): int
    {
        $count = 0;
        foreach ($this->seedData->contacts as $contactData) {
            $contact = Contact::factory()->create([
                'account_id' => $this->account->id,
                'name' => $contactData['name'],
                'email' => $contactData['email'],
            ]);

            foreach ($contactData['conversations'] as $conversationData) {
                $this->createConversation($contact, $conversationData);
            }
            
            $count++;
        }
        return $count;
    }

    /**
     * Seed canned responses using Laravel factories.
     */
    private function seedCannedResponses(int $count = 50): int
    {
        $responses = CannedResponse::factory()
            ->count($count)
            ->create(['account_id' => $this->account->id]);
            
        return $responses->count();
    }

    /**
     * Create account user relationship.
     */
    private function createAccountUser(User $user, array $userData): void
    {
        $roleName = $userData['role'] ?? 'agent';
        $role = AccountUserRole::fromName($roleName);
        
        $accountUserData = [
            'role' => $role,
        ];

        if (!empty($userData['custom_role'])) {
            $customRole = $this->account->customRoles()
                ->where('name', $userData['custom_role'])
                ->first();
            
            if ($customRole) {
                $accountUserData['custom_role_id'] = $customRole->id;
            }
        }

        AccountUser::factory()->create([
            'account_id' => $this->account->id,
            'user_id' => $user->id,
            ...$accountUserData,
        ]);
    }

    /**
     * Add user to teams.
     */
    private function addUserToTeams(User $user, array $teams): void
    {
        foreach ($teams as $teamName) {
            $team = $this->account->teams()
                ->where('name', 'LIKE', "%{$teamName}%")
                ->first();

            if ($team) {
                $team->members()->syncWithoutDetaching([$user->id]);
            }
        }
    }

    /**
     * Create conversation with messages.
     */
    private function createConversation(Contact $contact, array $conversationData): void
    {
        $inbox = $this->findInboxByChannel($conversationData['channel']);
        
        if (!$inbox) {
            return;
        }

        $contactInbox = ContactInbox::factory()->create([
            'contact_id' => $contact->id,
            'inbox_id' => $inbox->id,
            'source_id' => $conversationData['source_id'] ?? Str::random(10),
        ]);

        $assignee = null;
        if (!empty($conversationData['assignee'])) {
            $assignee = User::where('email', $conversationData['assignee'])->first();
        }

        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
            'contact_inbox_id' => $contactInbox->id,
            'assignee_id' => $assignee?->id,
            'priority' => $this->mapPriorityToConstant($conversationData['priority'] ?? null),
            'status' => Conversation::STATUS_OPEN,
        ]);

        $this->createMessages($conversation, $conversationData['messages']);

        if (!empty($conversationData['labels'])) {
            $this->attachLabels($conversation, $conversationData['labels']);
        }
    }

    /**
     * Find inbox by channel type.
     */
    private function findInboxByChannel(string $channelType): ?Inbox
    {
        $channelClassMap = [
            'WebWidget' => WebWidget::class,
            'FacebookPage' => FacebookPage::class,
            'TwitterProfile' => TwitterProfile::class,
            'Whatsapp' => Whatsapp::class,
            'Sms' => Sms::class,
            'Email' => Email::class,
            'Api' => Api::class,
            'Telegram' => Telegram::class,
            'Line' => Line::class,
            'Voice' => Voice::class,
        ];

        $channelClass = $channelClassMap[$channelType] ?? null;
        if (!$channelClass) {
            return null;
        }

        return $this->account->inboxes()
            ->where('channel_type', $channelClass)
            ->first();
    }

    /**
     * Create messages using Laravel factories.
     */
    private function createMessages(Conversation $conversation, array $messages): void
    {
        foreach ($messages as $messageData) {
            $sender = $this->findMessageSender($conversation, $messageData);

            Message::factory()->create([
                'account_id' => $this->account->id,
                'inbox_id' => $conversation->inbox_id,
                'conversation_id' => $conversation->id,
                'sender_type' => $sender ? get_class($sender) : null,
                'sender_id' => $sender?->id,
                'content' => $messageData['content'],
                'message_type' => $this->mapMessageTypeToConstant($messageData['message_type']),
            ]);
        }
    }

    /**
     * Find message sender.
     */
    private function findMessageSender(Conversation $conversation, array $messageData)
    {
        if ($messageData['message_type'] === 'incoming') {
            return $conversation->contact;
        }

        if (!empty($messageData['sender'])) {
            return User::where('email', $messageData['sender'])->first();
        }

        return null;
    }

    /**
     * Attach labels to conversation.
     */
    private function attachLabels(Conversation $conversation, array $labelNames): void
    {
        $labels = $this->account->labels()
            ->whereIn('title', $labelNames)
            ->get();

        $conversation->labels()->attach($labels->pluck('id'));
    }

    /**
     * Get seeding statistics.
     */
    public function getStats(): array
    {
        return [
            'total_teams' => count($this->seedData->teams),
            'total_custom_roles' => count($this->seedData->customRoles),
            'total_users' => count($this->seedData->users),
            'total_labels' => count($this->seedData->labels),
            'total_contacts' => count($this->seedData->contacts),
            'total_conversations' => collect($this->seedData->contacts)
                ->sum(fn($contact) => count($contact['conversations'])),
            'total_messages' => collect($this->seedData->contacts)
                ->flatMap(fn($contact) => $contact['conversations'])
                ->sum(fn($conv) => count($conv['messages'])),
        ];
    }

    /**
     * Map priority string to constant.
     */
    private function mapPriorityToConstant(?string $priority): int
    {
        if ($priority === null) {
            return Conversation::PRIORITY_NONE;
        }

        return match (strtolower($priority)) {
            'low' => Conversation::PRIORITY_LOW,
            'medium' => Conversation::PRIORITY_MEDIUM,
            'high' => Conversation::PRIORITY_HIGH,
            'urgent' => Conversation::PRIORITY_URGENT,
            default => Conversation::PRIORITY_NONE,
        };
    }

    /**
     * Map message type string to constant.
     */
    private function mapMessageTypeToConstant(string $messageType): int
    {
        return match (strtolower($messageType)) {
            'incoming' => Message::TYPE_INCOMING,
            'outgoing' => Message::TYPE_OUTGOING,
            'activity' => Message::TYPE_ACTIVITY,
            'template' => Message::TYPE_TEMPLATE,
            default => Message::TYPE_INCOMING,
        };
    }
}