@extends('emails.layouts.base')

@section('title', 'CNAME Setup Instructions')

@section('content')
<div class="email-content">
    <h2>CNAME Setup Instructions for {{ $portal->name }}</h2>
    
    <p>Hello,</p>
    
    <p>Thank you for setting up a custom domain for your portal <strong>{{ $portal->name }}</strong>. 
    Please follow the instructions below to complete the DNS configuration.</p>
    
    <div class="conversation-details">
        <h3>Portal Information</h3>
        <ul>
            <li><strong>Portal Name:</strong> {{ $portal->name }}</li>
            <li><strong>Portal Slug:</strong> {{ $portal->slug }}</li>
            <li><strong>Custom Domain:</strong> {{ $portal->custom_domain ?? 'Not set' }}</li>
            <li><strong>Default URL:</strong> {{ $portal->slug }}.{{ config('app.portal_domain', config('app.name', 'chatwoot') . '.com') }}</li>
        </ul>
    </div>
    
    <div class="conversation-details">
        <h3>Required DNS Records</h3>
        
        @if($cname_record)
        <div style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 12px; margin: 12px 0; font-family: monospace;">
            <div><strong>Type:</strong> {{ $cname_record['type'] }}</div>
            <div><strong>Name:</strong> {{ $cname_record['name'] }}</div>
            <div><strong>Value:</strong> {{ $cname_record['value'] }}</div>
            <div><strong>TTL:</strong> {{ $cname_record['ttl'] }} seconds</div>
        </div>
        @endif
        
        @if($dns_records && count($dns_records) > 0)
        @foreach($dns_records as $record)
        <div style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 12px; margin: 12px 0; font-family: monospace;">
            <div><strong>Type:</strong> {{ $record['type'] ?? 'CNAME' }}</div>
            <div><strong>Name:</strong> {{ $record['name'] ?? $record['host'] ?? '' }}</div>
            <div><strong>Value:</strong> {{ $record['value'] ?? $record['target'] ?? '' }}</div>
            <div><strong>TTL:</strong> {{ $record['ttl'] ?? 300 }} seconds</div>
        </div>
        @endforeach
        @endif
    </div>
    
    <div class="conversation-details">
        <h3>Setup Steps</h3>
        <ol style="padding-left: 20px;">
            @foreach($verification_steps as $step)
            <li style="margin-bottom: 12px;">
                <strong>{{ $step['title'] }}</strong><br>
                <span style="color: #6c757d;">{{ $step['description'] }}</span>
            </li>
            @endforeach
        </ol>
    </div>
    
    <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; padding: 16px; margin: 20px 0;">
        <h3 style="color: #0c5460; margin-top: 0;">💡 Important Notes</h3>
        <ul style="color: #0c5460; margin: 0; padding-left: 20px;">
            <li>DNS changes can take up to 24-48 hours to propagate globally</li>
            <li>SSL certificates will be automatically provisioned once DNS is verified</li>
            <li>You can test DNS propagation using online tools like whatsmydns.net</li>
            <li>Contact support if you encounter any issues during setup</li>
        </ul>
    </div>
    
    @if($action_url)
    <div class="action-button">
        <a href="{{ $action_url }}" class="btn btn-primary">Portal Settings</a>
    </div>
    @endif
    
    <p>Once the DNS records are active and verified, your portal will be accessible at your custom domain with SSL encryption.</p>
    
    <p>If you need assistance with this setup, please don't hesitate to contact our support team.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection