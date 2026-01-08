@extends('emails.layouts.base')

@section('title', 'You Were Mentioned')

@section('content')
<div class="email-content">
    <h2>You Were Mentioned in a Conversation</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p>You have been mentioned in conversation <strong>[ID - {{ $conversation->display_id }}]</strong>.</p>
    
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
    <div class="mention-message">
        <h3>Message with Mention:</h3>
        <div class="message-content">
            <div class="message-sender">
                <strong>{{ $message->user->name ?? 'System' }}</strong> 
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
    
    <p>Please check the conversation and respond if needed.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection