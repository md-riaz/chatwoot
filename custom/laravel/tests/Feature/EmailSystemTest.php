<?php

namespace Tests\Feature;

use App\Mail\AgentNotifications\ConversationNotificationMail;
use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\User;
use App\Services\Email\BounceHandlingService;
use App\Services\Email\InboundEmailProcessor;
use App\Services\Email\LiquidTemplateService;
use App\Services\Email\TemplateResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_mailable_base_functionality()
    {
        $account = Account::factory()->create();
        
        // Create a test mailable that extends ApplicationMailable
        $mailable = new class($account) extends ApplicationMailable {
            protected function getViewName(): string
            {
                return 'emails.generic-notification';
            }
        };

        $this->assertInstanceOf(ApplicationMailable::class, $mailable);
        $this->assertEquals($account, $mailable->account);
    }

    public function test_email_configuration_exists()
    {
        $this->assertIsArray(config('email'));
        $this->assertArrayHasKey('brand', config('email'));
        $this->assertArrayHasKey('domains', config('email'));
        $this->assertArrayHasKey('templates', config('email'));
        $this->assertArrayHasKey('bounce', config('email'));
    }

    public function test_conversation_notification_mail_creation()
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);

        $mail = ConversationNotificationMail::conversationCreation($conversation, $user);

        $this->assertInstanceOf(ConversationNotificationMail::class, $mail);
        $this->assertEquals($conversation, $mail->conversation);
        $this->assertEquals($user, $mail->agent);
    }

    public function test_liquid_template_service_processes_variables()
    {
        $service = new LiquidTemplateService();
        
        $template = 'Hello {{ user.name }}, your conversation {{ conversation.display_id }} is ready.';
        $variables = [
            'user' => (object) ['name' => 'John Doe'],
            'conversation' => (object) ['display_id' => '12345'],
        ];

        $result = $service->process($template, $variables);
        
        $this->assertStringContainsString('Hello John Doe', $result);
        $this->assertStringContainsString('conversation 12345', $result);
    }

    public function test_template_resolver_service_resolves_templates()
    {
        $service = new TemplateResolverService();
        
        $template = $service->resolve('generic-notification');
        
        $this->assertIsString($template);
        $this->assertStringContainsString('emails.', $template);
    }

    public function test_bounce_handling_service_processes_bounces()
    {
        $service = new BounceHandlingService();
        
        $bounceData = [
            'email' => 'test@example.com',
            'bounce_type' => 'hard',
            'reason' => 'Mailbox does not exist',
            'timestamp' => now()->toISOString(),
        ];

        $result = $service->processBounceWebhook($bounceData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_inbound_email_processor_validates_data()
    {
        $processor = app(InboundEmailProcessor::class);
        
        $emailData = [
            'from' => 'customer@example.com',
            'to' => 'support@example.com',
            'subject' => 'Test Subject',
            'body' => 'Test message body',
        ];

        $result = $processor->process($emailData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_email_templates_exist()
    {
        $templates = [
            'emails.agent-notifications.conversation_creation',
            'emails.agent-notifications.conversation_assignment',
            'emails.agent-notifications.conversation_mention',
            'emails.conversation.conversation_transcript',
            'emails.portal.cname_instructions',
        ];

        foreach ($templates as $template) {
            $this->assertTrue(
                view()->exists($template),
                "Template {$template} does not exist"
            );
        }
    }

    public function test_email_layout_exists()
    {
        $this->assertTrue(
            view()->exists('emails.layouts.base'),
            'Base email layout does not exist'
        );
    }

    public function test_mail_can_be_sent()
    {
        Mail::fake();

        $account = Account::factory()->create();
        $user = User::factory()->create(['email' => 'agent@example.com']);
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);

        $mail = ConversationNotificationMail::conversationCreation($conversation, $user);
        
        Mail::to($user->email)->send($mail);

        Mail::assertSent(ConversationNotificationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}