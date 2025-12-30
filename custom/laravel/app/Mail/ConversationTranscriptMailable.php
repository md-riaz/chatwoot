<?php

namespace App\Mail;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConversationTranscriptMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function build()
    {
        return $this->subject("Conversation transcript - #" . ($this->conversation->display_id ?? $this->conversation->id))
            ->view('emails.conversation_transcript')
            ->with(['conversation' => $this->conversation]);
    }
}
