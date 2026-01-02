@extends('emails.layouts.base')

@section('title', 'New Conversation Created')

@section('content')
<div class="email-content">
    <h2>New Conversation Created</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p>A new conversation <strong>[ID - {{ $conversation->display_id }}]</strong> has been created in <strong>{{ $inbox->name ?? 'Unknown Inbox' }}</strong>.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Created:</strong> {{ $conversation->created_at->format('M d, Y \a\t g:i A') }}</li>
        </ul>
    </div>
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">View Conversation</a>
    </div>
    @endif
    
    <p>Please review and respond to this conversation as needed.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection