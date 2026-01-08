<?php

namespace Tests\Unit\Services\Messages;

use App\Models\Message;
use App\Services\Messages\StatusUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusUpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_perform_updates_message_status_to_sent()
    {
        $message = Message::factory()->create(['status' => Message::STATUS_FAILED]);
        
        $service = new StatusUpdateService($message, 'sent');
        $result = $service->perform();

        $this->assertTrue($result);
        $this->assertEquals(Message::STATUS_SENT, $message->fresh()->status);
    }

    public function test_perform_updates_message_status_to_failed_with_error()
    {
        $message = Message::factory()->create(['status' => Message::STATUS_SENT]);
        $errorMessage = 'Network timeout';
        
        $service = new StatusUpdateService($message, 'failed', $errorMessage);
        $result = $service->perform();

        $this->assertTrue($result);
        $message->refresh();
        $this->assertEquals(Message::STATUS_FAILED, $message->status);
        $this->assertEquals($errorMessage, $message->content_attributes['external_error']);
    }

    public function test_perform_clears_external_error_when_sent()
    {
        $message = Message::factory()->create([
            'status' => Message::STATUS_FAILED,
            'content_attributes' => ['external_error' => 'Previous error']
        ]);
        
        $service = new StatusUpdateService($message, 'sent');
        $result = $service->perform();

        $this->assertTrue($result);
        $message->refresh();
        $this->assertEquals(Message::STATUS_SENT, $message->status);
        $this->assertArrayNotHasKey('external_error', $message->content_attributes);
    }

    public function test_perform_rejects_invalid_status()
    {
        $message = Message::factory()->create(['status' => Message::STATUS_SENT]);
        
        $service = new StatusUpdateService($message, 'invalid_status');
        $result = $service->perform();

        $this->assertFalse($result);
        $this->assertEquals(Message::STATUS_SENT, $message->fresh()->status);
    }

    public function test_perform_rejects_read_to_delivered_transition()
    {
        $message = Message::factory()->create(['status' => Message::STATUS_READ]);
        
        $service = new StatusUpdateService($message, 'delivered');
        $result = $service->perform();

        $this->assertFalse($result);
        $this->assertEquals(Message::STATUS_READ, $message->fresh()->status);
    }

    public function test_perform_allows_valid_status_transitions()
    {
        $validTransitions = [
            [Message::STATUS_SENT, 'delivered', Message::STATUS_DELIVERED],
            [Message::STATUS_DELIVERED, 'read', Message::STATUS_READ],
            [Message::STATUS_SENT, 'failed', Message::STATUS_FAILED],
            [Message::STATUS_FAILED, 'sent', Message::STATUS_SENT],
        ];

        foreach ($validTransitions as [$initialStatus, $newStatus, $expectedStatus]) {
            $message = Message::factory()->create(['status' => $initialStatus]);
            
            $service = new StatusUpdateService($message, $newStatus);
            $result = $service->perform();

            $this->assertTrue($result, "Failed transition from {$initialStatus} to {$newStatus}");
            $this->assertEquals($expectedStatus, $message->fresh()->status);
        }
    }
}