@extends('emails.layouts.base')

@section('title', 'Account Deletion Scheduled')

@section('content')
<div class="email-content">
    <h2>Your {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Account Deletion Has Been Scheduled</h2>
    
    <p>Hello,</p>
    
    <p>We have received your request to delete your {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} account <strong>{{ $metadata['account_name'] ?? $account->name ?? 'Unknown' }}</strong>.</p>
    
    <div class="conversation-details">
        <h3>Deletion Details:</h3>
        <ul>
            <li><strong>Account Name:</strong> {{ $metadata['account_name'] ?? $account->name ?? 'Unknown' }}</li>
            <li><strong>Scheduled Deletion Date:</strong> {{ $metadata['deletion_date'] ?? 'Unknown' }}</li>
            <li><strong>Reason:</strong> {{ $metadata['reason'] ?? 'User requested' }}</li>
            <li><strong>Request Date:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</li>
        </ul>
    </div>
    
    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 16px; margin: 20px 0;">
        <h3 style="color: #856404; margin-top: 0;">⚠️ Important Information</h3>
        <ul style="color: #856404; margin: 0; padding-left: 20px;">
            <li>Your account will be permanently deleted on the scheduled date</li>
            <li>All conversations, contacts, and data will be permanently removed</li>
            <li>This action cannot be undone after the deletion date</li>
            <li>You can cancel this deletion request before the scheduled date</li>
        </ul>
    </div>
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">Manage Account Settings</a>
    </div>
    @endif
    
    <p>If you did not request this deletion or wish to cancel it, please contact our support team immediately or visit your account settings.</p>
    
    <p>Thank you for using {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }}.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection