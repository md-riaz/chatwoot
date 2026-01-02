@extends('emails.layouts.base')

@section('title', 'New Message in Participating Conversation')

@section('content')
<div class="email-content">
    <h2>New Message in Your Participating Conversation</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p>There's a new message in a conversation <strong>[ID - {{ $conversation->display_id }}]</strong> you're participating in.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Assigned to:</strong> {{ $conversation->assignee->name ?? 'Unassigned' }}</li>
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
        <a href="{{ $action_url }}" class="btn btn-primary">View Conversation</a>
    </div>
    @endif
    
    <p>You're receiving this notification because you've participated in this conversation.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? 'Chatwoot' }} Team</p>
</div>
@endsection