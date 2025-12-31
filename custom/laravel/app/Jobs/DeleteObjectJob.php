<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteObjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'low';

    protected $object;
    protected $user;
    protected $ip;

    public function __construct($object, $user = null, $ip = null)
    {
        $this->object = $object;
        $this->user = $user;
        $this->ip = $ip;
    }

    public function handle(): void
    {
        $this->purgeHeavyAssociations($this->object);

        try {
            $this->object->delete();
        } catch (\Exception $e) {
            Log::warning('DeleteObjectJob: deletion failed', ['error' => $e->getMessage()]);
        }

        $this->processPostDeletionTasks($this->object, $this->user, $this->ip);
    }

    protected function processPostDeletionTasks($object, $user = null, $ip = null): void
    {
        // noop for now — extend in project-specific cases
    }

    protected function purgeHeavyAssociations($object): void
    {
        $map = [
            \App\Models\Account::class => ['conversations', 'contacts', 'inboxes', 'reportingEvents'],
            \App\Models\Inbox::class => ['conversations', 'contactInboxes', 'reportingEvents'],
        ];

        foreach ($map as $klass => $assocs) {
            if ($object instanceof $klass) {
                foreach ($assocs as $assoc) {
                    if (method_exists($object, $assoc)) {
                        try {
                            $relation = $object->$assoc();
                            $this->batchDestroy($relation);
                        } catch (\Throwable $e) {
                            Log::warning('DeleteObjectJob: purge association failed', ['assoc' => $assoc, 'error' => $e->getMessage()]);
                        }
                    }
                }
            }
        }
    }

    protected function batchDestroy($relation): void
    {
        if (method_exists($relation, 'chunk')) {
            $relation->chunk(5000, function ($items) {
                foreach ($items as $item) {
                    try {
                        $item->delete();
                    } catch (\Throwable $e) {
                        Log::warning('DeleteObjectJob: item delete failed', ['error' => $e->getMessage()]);
                    }
                }
            });
        }
    }
}
