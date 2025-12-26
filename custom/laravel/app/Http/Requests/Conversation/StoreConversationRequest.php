<?php

namespace App\Http\Requests\Conversation;

use App\Data\Conversation\ConversationData;
use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy check
    }

    public function rules(): array
    {
        return [
            'inbox_id' => ['required', 'integer', 'exists:inboxes,id'],
            'contact_id' => ['required', 'integer', 'exists:contacts,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'status' => ['nullable', 'integer', 'in:0,1,2,3'],
            'priority' => ['nullable', 'integer', 'in:0,1,2,3,4'],
            'custom_attributes' => ['nullable', 'array'],
        ];
    }
}
