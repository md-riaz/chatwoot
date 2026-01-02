@extends('emails.layouts.base')

@section('title', 'New Message in Assigned Conversation')

@section('content')
<div class="email-content">
    <h2>New Message in Your Assigned Conversation</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p>There's a new message in your assigned conversation <strong>[ID - {{ $conversation->display_id }}]</strong>.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
        </ul>
    </div>
    
    @if($message)
    <div class="new-message">
        <h3>New Message:</h3>
        <div class="message-content">
            <div class="message-sender">
                <strong>{{ $message->sender_type === 'Contact' ? ($conversation->contact->name ?? 'Customer') : ($message->user->name ?? 'Agent') }}</strong>
                <span class="message-time">{{ $message->created_at->format('M d, Y \a\t g:i A') }}</span>
            </div>
            <div class="message-text">
                {{ $message->content }}
            </div>
        </div>
    </div>
    @endif
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">View & Respond</a>
    </div>
    @endif
    
    <p>Please respond to this message promptly to maintain good customer service.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection