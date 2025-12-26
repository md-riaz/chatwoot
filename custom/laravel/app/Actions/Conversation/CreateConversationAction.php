<?php

namespace App\Actions\Conversation;

use App\Data\Conversation\ConversationData;
use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(ConversationData $data): Conversation
    {
        $conversationData = $data->toArray();
        $conversationData['uuid'] = (string) Str::uuid();
        $conversationData['last_activity_at'] = now();

        $conversation = $this->conversationRepository->create($conversationData);

        // Trigger event
        // event(new ConversationCreated($conversation));

        return $conversation;
    }

    public function rules(): array
    {
        return ConversationData::rules();
    }
}
