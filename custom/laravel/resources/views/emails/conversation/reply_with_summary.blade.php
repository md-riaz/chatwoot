@extends('emails.layouts.base')

@section('title', 'Re: Conversation #' . $conversation->display_id)

@section('content')
<div class="email-content">
    @if($message)
    <div class="new-message">
        <div class="message-content">
            <div class="message-sender">
                <strong>{{ $message->user->name ?? 'Support Agent' }}</strong>
                <span class="message-time">{{ $message->created_at->format('M d, Y \a\t g:i A') }}</span>
            </div>
            <div class="message-text">
                {!! nl2br(e($message->content)) !!}
            </div>
        </div>
    </div>
    @endif
    
    @if($with_summary && count($messages) > 1)
    <div class="conversation-summary">
        <h3>Conversation Summary</h3>
        <p><strong>Conversation ID:</strong> {{ $conversation->display_id }}</p>
        <p><strong>Total Messages:</strong> {{ count($messages) }}</p>
        
        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 4px; padding: 12px; margin-top: 12px;">
            @foreach(array_slice($messages, -5) as $msg)
            <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f8f9fa;">
                <div style="font-weight: 600; color: #1f93ff; font-size: 14px;">
                    {{ $msg['sender_type'] === 'Contact' ? ($conversation->contact->name ?? 'Customer') : ($msg['user']['name'] ?? 'Agent') }}
                    <span style="font-weight: normal; color: #6c757d; margin-left: 8px;">
                        {{ \Carbon\Carbon::parse($msg['created_at'])->format('M d, g:i A') }}
                    </span>
                </div>
                <div style="margin-top: 4px; font-size: 14px;">
                    {{ Str::limit($msg['content'], 150) }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    @if($attachments && count($attachments) > 0)
    <div class="conversation-details">
        <h3>Attachments</h3>
        <ul>
            @foreach($attachments as $attachment)
            <li>
                <a href="{{ $attachment['url'] }}" style="color: #1f93ff; text-decoration: none;">
                    {{ $attachment['filename'] }}
                </a>
                <span style="color: #6c757d; font-size: 12px;">
                    ({{ number_format($attachment['file_size'] / 1024, 1) }} KB)
                </span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">View Full Conversation</a>
    </div>
    @endif
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <p>You're receiving this email because you're involved in this conversation. 
        To reply, simply respond to this email or click the link above to view the full conversation.</p>
    </div>
</div>
@endsection