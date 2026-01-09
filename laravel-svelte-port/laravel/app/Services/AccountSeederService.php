<?php

namespace App\Services;

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class AccountSeederService
{
    private Account $account;
    private array $accountData;

    public function __construct(Account $account)
    {
        if (!config('app.enable_account_seeding', !app()->environment('production'))) {
            throw new \Exception('Account Seeding is not allowed.');
        }

        $this->account = $account;
        $this->accountData = $this->loadSeedData();
    }

    public function perform(): void
    {
        $this->setUpAccount();
        $this->seedTeams();
        $this->seedCustomRoles();
        $this->setUpUsers();
        $this->seedLabels();
        $this->seedCannedResponses();
        $this->seedInboxes();
        $this->seedContacts();
    }

    private function loadSeedData(): array
    {
        $yamlContent = Storage::get('seed_data.yml');
        return Yaml::parse($yamlContent);
    }

    private function setUpAccount(): void
    {
        // Clean up existing data
        $this->account->teams()->delete();
        $this->account->conversations()->delete();
        $this->account->labels()->delete();
        $this->account->inboxes()->delete();
        $this->account->contacts()->delete();
        $this->account->customRoles()->delete();
        $this->account->cannedResponses()->delete();
    }

    private function seedTeams(): void
    {
        foreach ($this->accountData['teams'] as $teamName) {
            Team::create([
                'account_id' => $this->account->id,
                'name' => $teamName,
            ]);
        }
    }

    private function seedCustomRoles(): void
    {
        if (!isset($this->accountData['custom_roles'])) {
            return;
        }

        foreach ($this->accountData['custom_roles'] as $roleData) {
            CustomRole::create([
                'account_id' => $this->account->id,
                'name' => $roleData['name'],
                'description' => $roleData['description'],
                'permissions' => $roleData['permissions'],
            ]);
        }
    }

    private function seedLabels(): void
    {
        foreach ($this->accountData['labels'] as $labelData) {
            Label::create([
                'account_id' => $this->account->id,
                'title' => $labelData['title'],
                'color' => $labelData['color'],
                'show_on_sidebar' => $labelData['show_on_sidebar'],
            ]);
        }
    }

    private function setUpUsers(): void
    {
        foreach ($this->accountData['users'] as $userData) {
            $user = $this->createUserRecord($userData);
            $this->createAccountUser($user, $userData);
            
            if (!empty($userData['team'])) {
                $this->addUserToTeams($user, $userData['team']);
            }
        }
    }

    private function createUserRecord(array $userData): User
    {
        $user = User::firstOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'password' => Hash::make('Password1!.'),
                'email_verified_at' => now(),
            ]
        );

        return $user;
    }

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

        AccountUser::firstOrCreate(
            [
                'account_id' => $this->account->id,
                'user_id' => $user->id,
            ],
            $accountUserData
        );
    }

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

    private function seedCannedResponses(int $count = 50): void
    {
        for ($i = 0; $i < $count; $i++) {
            CannedResponse::create([
                'account_id' => $this->account->id,
                'content' => $this->generateRandomContent(),
                'short_code' => Str::random(10),
            ]);
        }
    }

    private function generateRandomContent(): string
    {
        $responses = [
            'Thank you for contacting us. We will get back to you shortly.',
            'We appreciate your patience while we resolve this issue.',
            'Your request has been received and is being processed.',
            'Please provide more details so we can assist you better.',
            'We are working on your request and will update you soon.',
            'Thank you for choosing our service. How can we help you today?',
            'We have received your message and will respond within 24 hours.',
            'Your feedback is important to us. Thank you for sharing.',
            'We are here to help. Please let us know if you need anything else.',
            'Your issue has been escalated to our technical team.',
        ];

        return $responses[array_rand($responses)];
    }

    private function seedContacts(): void
    {
        foreach ($this->accountData['contacts'] as $contactData) {
            $contact = Contact::firstOrCreate(
                [
                    'account_id' => $this->account->id,
                    'email' => $contactData['email'],
                ],
                [
                    'name' => $contactData['name'],
                ]
            );

            foreach ($contactData['conversations'] as $conversationData) {
                $this->createConversation($contact, $conversationData);
            }
        }
    }

    private function createConversation(Contact $contact, array $conversationData): void
    {
        $inbox = $this->findInboxByChannel($conversationData['channel']);
        
        if (!$inbox) {
            return;
        }

        $contactInbox = ContactInbox::firstOrCreate([
            'contact_id' => $contact->id,
            'inbox_id' => $inbox->id,
            'source_id' => $conversationData['source_id'] ?? Str::random(10),
        ]);

        $assignee = null;
        if (!empty($conversationData['assignee'])) {
            $assignee = User::where('email', $conversationData['assignee'])->first();
        }

        $conversation = Conversation::create([
            'account_id' => $this->account->id,
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
            'assignee_id' => $assignee?->id,
            'priority' => $conversationData['priority'] ?? null,
            'status' => 'open',
        ]);

        $this->createMessages($conversation, $conversationData['messages']);

        if (!empty($conversationData['labels'])) {
            $this->attachLabels($conversation, $conversationData['labels']);
        }
    }

    private function findInboxByChannel(string $channelType): ?Inbox
    {
        // Map channel type names to full class names for Laravel polymorphic relationships
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

    private function createMessages(Conversation $conversation, array $messages): void
    {
        foreach ($messages as $messageData) {
            $sender = $this->findMessageSender($conversation, $messageData);

            Message::create([
                'account_id' => $this->account->id,
                'inbox_id' => $conversation->inbox_id,
                'conversation_id' => $conversation->id,
                'sender_type' => $sender ? get_class($sender) : null,
                'sender_id' => $sender?->id,
                'content' => $messageData['content'],
                'message_type' => $messageData['message_type'],
            ]);
        }
    }

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

    private function attachLabels(Conversation $conversation, array $labelNames): void
    {
        $labels = $this->account->labels()
            ->whereIn('title', $labelNames)
            ->get();

        $conversation->labels()->attach($labels->pluck('id'));
    }

    private function seedInboxes(): void
    {
        $companyData = $this->accountData['company'];
        
        $this->seedWebsiteInbox($companyData);
        $this->seedFacebookInbox($companyData);
        $this->seedTwitterInbox($companyData);
        $this->seedWhatsappInbox($companyData);
        $this->seedSmsInbox($companyData);
        $this->seedEmailInbox($companyData);
        $this->seedApiInbox($companyData);
        $this->seedTelegramInbox($companyData);
        $this->seedLineInbox($companyData);
        $this->seedVoiceInbox($companyData);
    }

    private function seedWebsiteInbox(array $companyData): void
    {
        $channel = WebWidget::create([
            'account_id' => $this->account->id,
            'website_url' => "https://{$companyData['domain']}",
            'website_token' => 'wt_' . bin2hex(random_bytes(16)), // Generate unique token
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => WebWidget::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Website",
        ]);
    }

    private function seedFacebookInbox(array $companyData): void
    {
        $channel = FacebookPage::create([
            'account_id' => $this->account->id,
            'user_access_token' => Str::random(32),
            'page_access_token' => Str::random(32),
            'page_id' => Str::random(16),
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => FacebookPage::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Facebook",
        ]);
    }

    private function seedTwitterInbox(array $companyData): void
    {
        $channel = TwitterProfile::create([
            'account_id' => $this->account->id,
            'twitter_access_token' => Str::random(32),
            'twitter_access_token_secret' => Str::random(32),
            'profile_id' => '123',
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => TwitterProfile::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Twitter",
        ]);
    }

    private function seedWhatsappInbox(array $companyData): void
    {
        $channel = Whatsapp::create([
            'account_id' => $this->account->id,
            'phone_number' => '+1234567890',
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Whatsapp::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Whatsapp",
        ]);
    }

    private function seedSmsInbox(array $companyData): void
    {
        $channel = Sms::create([
            'account_id' => $this->account->id,
            'phone_number' => '+1234567891',
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Sms::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Mobile",
        ]);
    }

    private function seedEmailInbox(array $companyData): void
    {
        $channel = Email::create([
            'account_id' => $this->account->id,
            'email' => "test@{$companyData['domain']}",
            'forward_to_email' => "test_fwd@{$companyData['domain']}",
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Email::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Email",
        ]);
    }

    private function seedApiInbox(array $companyData): void
    {
        $channel = Api::create([
            'account_id' => $this->account->id,
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Api::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} API",
        ]);
    }

    private function seedTelegramInbox(array $companyData): void
    {
        $channel = Telegram::create([
            'account_id' => $this->account->id,
            'bot_name' => $companyData['name'],
            'bot_token' => Str::random(32),
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Telegram::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Telegram",
        ]);
    }

    private function seedLineInbox(array $companyData): void
    {
        $channel = Line::create([
            'account_id' => $this->account->id,
            'line_channel_id' => Str::random(16),
            'line_channel_secret' => Str::random(32),
            'line_channel_token' => Str::random(32),
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Line::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Line",
        ]);
    }

    private function seedVoiceInbox(array $companyData): void
    {
        $channel = Voice::create([
            'account_id' => $this->account->id,
            'phone_number' => '+1234567890',
            'provider' => 'twilio',
            'provider_config' => [
                'account_sid' => 'demo_account_sid',
                'auth_token' => 'demo_auth_token',
                'api_key_sid' => 'demo_api_key_sid',
                'api_key_secret' => 'demo_api_key_secret',
                'twiml_app_sid' => 'demo_twiml_app_sid',
            ],
        ]);

        Inbox::create([
            'channel_id' => $channel->id,
            'channel_type' => Voice::class,
            'account_id' => $this->account->id,
            'name' => "{$companyData['name']} Voice",
        ]);
    }
}