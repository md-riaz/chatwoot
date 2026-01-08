@extends('emails.layouts.base')

@section('title', 'Conversation Transcript #' . ($conversation->display_id ?? $conversation->id))

@section('content')
<div class="email-content">
    <h2>Conversation Transcript</h2>
    
    <div class="conversation-details">
        <h3>Conversation Details</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id ?? $conversation->id }}</li>
            <li><strong>Inbox:</strong> {{ $conversation->inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Created:</strong> {{ $conversation->created_at->format('M d, Y \a\t g:i A') }}</li>
            @if($conversation->resolved_at)
            <li><strong>Resolved:</strong> {{ $conversation->resolved_at->format('M d, Y \a\t g:i A') }}</li>
            @endif
            <li><strong>Total Messages:</strong> {{ $conversation->messages->count() }}</li>
        </ul>
    </div>
    
    <div class="conversation-summary">
        <h3>Complete Message History</h3>
        
        <div style="border: 1px solid #e9ecef; border-radius: 4px; padding: 0; margin-top: 12px;">
            @forelse($conversation->messages as $index => $message)
            <div style="padding: 16px; {{ $index < $conversation->messages->count() - 1 ? 'border-bottom: 1px solid #f8f9fa;' : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-weight: 600; color: {{ $message->message_type === 'incoming' ? '#28a745' : '#1f93ff' }};">
                        {{ $message->message_type === 'incoming' ? ($conversation->contact->name ?? 'Customer') : ($message->user->name ?? 'Agent') }}
                    </div>
                    <div style="color: #6c757d; font-size: 12px;">
                        {{ $message->created_at->format('M d, Y \a\t g:i A') }}
                    </div>
                </div>
                
                <div style="color: #333; line-height: 1.5;">
                    {!! nl2br(e($message->content)) !!}
                </div>
                
                @if($message->attachments && $message->attachments->count() > 0)
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #f8f9fa;">
                    <div style="font-size: 12px; color: #6c757d; margin-bottom: 4px;">Attachments:</div>
                    @foreach($message->attachments as $attachment)
                    <div style="font-size: 12px;">
                        <a href="{{ $attachment->file_url }}" style="color: #1f93ff; text-decoration: none;">
                            📎 {{ $attachment->file_name }}
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div style="padding: 16px; text-align: center; color: #6c757d;">
                No messages in this conversation.
            </div>
            @endforelse
        </div>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <p>This is a complete transcript of conversation #{{ $conversation->display_id ?? $conversation->id }}. 
        All messages and attachments from this conversation are included above.</p>
    </div>
</div>
@endsection
