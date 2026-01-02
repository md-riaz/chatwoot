@extends('emails.layouts.base')

@section('title', 'SLA Missed - Resolution')

@section('content')
<div class="email-content">
    <h2 style="color: #e74c3c;">⚠️ SLA Missed - Resolution</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p><strong style="color: #e74c3c;">URGENT:</strong> The SLA for resolution has been missed for conversation <strong>[ID - {{ $conversation->display_id }}]</strong>.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Created:</strong> {{ $conversation->created_at->format('M d, Y \a\t g:i A') }}</li>
            <li><strong>Total Duration:</strong> {{ $conversation->created_at->diffForHumans() }}</li>
        </ul>
    </div>
    
    @if($conversation->sla_policy)
    <div class="sla-details">
        <h3>SLA Policy Details:</h3>
        <ul>
            <li><strong>Policy:</strong> {{ $conversation->sla_policy->name }}</li>
            <li><strong>Resolution Time:</strong> {{ $conversation->sla_policy->resolution_time_threshold }} minutes</li>
            <li><strong>Threshold Exceeded By:</strong> 
                {{ max(0, $conversation->created_at->diffInMinutes(now()) - $conversation->sla_policy->resolution_time_threshold) }} minutes
            </li>
        </ul>
    </div>
    @endif
    
    <div class="conversation-summary">
        <h3>Conversation Summary:</h3>
        <ul>
            <li><strong>Total Messages:</strong> {{ $conversation->messages->count() }}</li>
            <li><strong>Agent Messages:</strong> {{ $conversation->messages->where('message_type', 'outgoing')->count() }}</li>
            <li><strong>Customer Messages:</strong> {{ $conversation->messages->where('message_type', 'incoming')->count() }}</li>
            <li><strong>Last Agent Response:</strong> {{ $conversation->messages->where('message_type', 'outgoing')->last()?->created_at?->diffForHumans() ?? 'Never' }}</li>
        </ul>
    </div>
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-danger">Resolve Immediately</a>
    </div>
    @endif
    
    <p><strong>Action Required:</strong> Please resolve this conversation immediately or escalate to a supervisor.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection