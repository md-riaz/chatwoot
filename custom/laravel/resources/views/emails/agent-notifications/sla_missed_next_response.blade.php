@extends('emails.layouts.base')

@section('title', 'SLA Missed - Next Response')

@section('content')
<div class="email-content">
    <h2 style="color: #e74c3c;">⚠️ SLA Missed - Next Response</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p><strong style="color: #e74c3c;">URGENT:</strong> The SLA for next response has been missed for conversation <strong>[ID - {{ $conversation->display_id }}]</strong>.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Last Customer Message:</strong> {{ $conversation->last_activity_at?->format('M d, Y \a\t g:i A') ?? 'Unknown' }}</li>
            <li><strong>Time Since Last Activity:</strong> {{ $conversation->last_activity_at?->diffForHumans() ?? 'Unknown' }}</li>
        </ul>
    </div>
    
    @if($conversation->sla_policy)
    <div class="sla-details">
        <h3>SLA Policy Details:</h3>
        <ul>
            <li><strong>Policy:</strong> {{ $conversation->sla_policy->name }}</li>
            <li><strong>Next Response Time:</strong> {{ $conversation->sla_policy->next_response_time_threshold }} minutes</li>
            <li><strong>Threshold Exceeded By:</strong> 
                {{ max(0, ($conversation->last_activity_at?->diffInMinutes(now()) ?? 0) - $conversation->sla_policy->next_response_time_threshold) }} minutes
            </li>
        </ul>
    </div>
    @endif
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-danger">Respond Immediately</a>
    </div>
    @endif
    
    <p><strong>Action Required:</strong> Please respond to this conversation immediately to minimize the SLA breach impact.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? 'Chatwoot' }} Team</p>
</div>
@endsection