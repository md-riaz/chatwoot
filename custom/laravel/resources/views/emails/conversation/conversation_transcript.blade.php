@extends('emails.layouts.base')

@section('title', 'Conversation Transcript #' . $conversation->display_id)

@section('content')
<div class="email-content">
    <h2>Conversation Transcript</h2>
    
    <div class="conversation-details">
        <h3>Conversation Details</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Created:</strong> {{ $conversation->created_at->format('M d, Y \a\t g:i A') }}</li>
            @if($conversation->resolved_at)
            <li><strong>Resolved:</strong> {{ $conversation->resolved_at->format('M d, Y \a\t g:i A') }}</li>
            @endif
            <li><strong>Total Messages:</strong> {{ count($messages) }}</li>
        </ul>
    </div>
    
    <div class="conversation-summary">
        <h3>Complete Message History</h3>
        
        <div style="border: 1px solid #e9ecef; border-radius: 4px; padding: 0; margin-top: 12px;">
            @foreach($messages as $index => $msg)
            <div style="padding: 16px; {{ $index < count($messages) - 1 ? 'border-bottom: 1px solid #f8f9fa;' : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-weight: 600; color: {{ $msg['message_type'] === 'incoming' ? '#28a745' : '#1f93ff' }};">
                        {{ $msg['message_type'] === 'incoming' ? ($conversation->contact->name ?? 'Customer') : ($msg['user']['name'] ?? 'Agent') }}
                    </div>
                    <div style="color: #6c757d; font-size: 12px;">
                        {{ \Carbon\Carbon::parse($msg['created_at'])->format('M d, Y \a\t g:i A') }}
                    </div>
                </div>
                
                <div style="color: #333; line-height: 1.5;">
                    {!! nl2br(e($msg['content'])) !!}
                </div>
                
                @if(isset($msg['attachments']) && count($msg['attachments']) > 0)
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #f8f9fa;">
                    <div style="font-size: 12px; color: #6c757d; margin-bottom: 4px;">Attachments:</div>
                    @foreach($msg['attachments'] as $attachment)
                    <div style="font-size: 12px;">
                        <a href="{{ $attachment['file_url'] }}" style="color: #1f93ff; text-decoration: none;">
                            📎 {{ $attachment['file_name'] }}
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">View in Dashboard</a>
    </div>
    @endif
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <p>This is a complete transcript of conversation #{{ $conversation->display_id }}. 
        All messages and attachments from this conversation are included above.</p>
    </div>
</div>
@endsection