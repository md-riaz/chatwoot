@extends('emails.layouts.base')

@section('title', 'SLA Missed - First Response')

@section('content')
<div class="email-content">
    <h2 style="color: #e74c3c;">⚠️ SLA Missed - First Response</h2>
    
    <p>Hello {{ $user->available_name ?? $user->name }},</p>
    
    <p><strong style="color: #e74c3c;">URGENT:</strong> The SLA for first response has been missed for conversation <strong>[ID - {{ $conversation->display_id }}]</strong>.</p>
    
    <div class="conversation-details">
        <h3>Conversation Details:</h3>
        <ul>
            <li><strong>Conversation ID:</strong> {{ $conversation->display_id }}</li>
            <li><strong>Inbox:</strong> {{ $inbox->name ?? 'Unknown' }}</li>
            <li><strong>Contact:</strong> {{ $conversation->contact->name ?? $conversation->contact->email ?? 'Unknown' }}</li>
            <li><strong>Status:</strong> {{ ucfirst($conversation->status) }}</li>
            <li><strong>Created:</strong> {{ $conversation->created_at->format('M d, Y \a\t g:i A') }}</li>
            <li><strong>Time Since Creation:</strong> {{ $conversation->created_at->diffForHumans() }}</li>
        </ul>
    </div>
    
    @if($conversation->sla_policy)
    <div class="sla-details">
        <h3>SLA Policy Details:</h3>
        <ul>
            <li><strong>Policy:</strong> {{ $conversation->sla_policy->name }}</li>
            <li><strong>First Response Time:</strong> {{ $conversation->sla_policy->first_response_time_threshold }} minutes</li>
            <li><strong>Threshold Exceeded By:</strong> 
                {{ max(0, $conversation->created_at->diffInMinutes(now()) - $conversation->sla_policy->first_response_time_threshold) }} minutes
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
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection