<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 120;

    public int $timeout = 180;

    public string $queue = 'deliveries';

    public int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        $message = Message::with('conversation.inbox.channel')->find($this->messageId);
        if (! $message) {
            return;
        }

        $channel = $message->conversation->inbox->channel ?? null;
        if (! $channel) {
            // No channel configured; mark as failed
            $message->update(['status' => Message::STATUS_FAILED]);
            return;
        }

        // Map channel model class to a service class name used in Laravel port.
        $channelClass = get_class($channel);

        // Short name like 'Whatsapp' from App\Models\Channels\Whatsapp
        $short = class_basename($channelClass);

        // Candidate service class names (in order). Projects can implement any of these.
        $candidates = [
            "App\\Services\\Channels\\{$short}\\SendOn{$short}Service",
            "App\\Services\\Channels\\SendOn{$short}Service",
            "App\\Services\\Channels\\{$short}SendService",
            "App\\Channels\\{$short}\\SendService",
        ];

        // Special-case FacebookPage -> Facebook/Instagram split like Rails
        if ($short === 'FacebookPage') {
            $additionalType = $message->conversation->additional_attributes['type'] ?? null;
            if ($additionalType === 'instagram_direct_message') {
                $candidates = array_merge(['App\\Services\\Channels\\Instagram\\SendOnInstagramService'], $candidates);
            } else {
                $candidates = array_merge(['App\\Services\\Channels\\Facebook\\SendOnFacebookService'], $candidates);
            }
        }

        $serviceFound = false;

        foreach ($candidates as $svcClass) {
            if (class_exists($svcClass)) {
                try {
                    $svc = new $svcClass(message: $message);
                    if (method_exists($svc, 'handle')) {
                        $svc->handle();
                    } elseif (method_exists($svc, 'perform')) {
                        $svc->perform();
                    }

                    $serviceFound = true;
                    break;
                } catch (\Exception $e) {
                    // Log and continue to try other candidates
                    report($e);
                }
            }
        }

        if (! $serviceFound) {
            // Fallback: mark sent so the system doesn't keep retrying endlessly
            try {
                $message->update(['status' => Message::STATUS_SENT]);
                $message->sendUpdateEvent();
            } catch (\Exception $e) {
                $message->update(['status' => Message::STATUS_FAILED]);
            }
        }
    }
}
