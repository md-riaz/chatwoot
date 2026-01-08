@extends('emails.layouts.base')

@section('title', 'Conversation Assigned')

@section('content')
<div class="email-content">
    <h2>Conversation Assigned to You</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p>A conversation <strong>[ID - {{ $conversation->display_id }}]</strong> has been assigned to you.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Assigned:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</li>
        </ul>
    </div>
    
    @if($conversation->messages->count() > 0)
    <div class="latest-message">
        <h3>Latest Message:</h3>
        <div class="message-content">
            {{ Str::limit($conversation->messages->last()->content, 200) }}
        </div>
    </div>
    @endif
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">View & Respond</a>
    </div>
    @endif
    
    <p>Please review and respond to this conversation promptly.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection