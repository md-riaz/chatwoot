@extends('emails.layouts.base')

@section('title', 'Contact Import Completed')

@section('content')
<div class="email-content">
    <h2>Contact Import Completed</h2>
    
    <p>Hello,</p>
    
    <p>Your contact import process has been completed successfully.</p>
    
    <div class="conversation-details">
        <h3>Import Summary:</h3>
        <ul>
            <li><strong>Successfully Imported:</strong> {{ $metadata['imported_contacts'] ?? 0 }} contacts</li>
            <li><strong>Failed Imports:</strong> {{ $metadata['failed_contacts'] ?? 0 }} contacts</li>
            <li><strong>Completion Time:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</li>
        </ul>
    </div>
    
    @if(($metadata['failed_contacts'] ?? 0) > 0)
    <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 16px; margin: 20px 0;">
        <h3 style="color: #721c24; margin-top: 0;">⚠️ Failed Imports</h3>
        <p style="color: #721c24; margin: 0;">
            {{ $metadata['failed_contacts'] }} contacts could not be imported due to validation errors or duplicate entries.
            @if($action_url && str_contains($action_url, 'blob'))
                You can download a file with the failed records to review and fix the issues.
            @endif
        </p>
    </div>
    @endif
    
    @if(($metadata['imported_contacts'] ?? 0) > 0)
    <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 16px; margin: 20px 0;">
        <h3 style="color: #155724; margin-top: 0;">✅ Import Successful</h3>
        <p style="color: #155724; margin: 0;">
            {{ $metadata['imported_contacts'] }} contacts have been successfully imported and are now available in your contact list.
        </p>
    </div>
    @endif
    
    @if($action_url)
    <div class="action-button">
        @if(str_contains($action_url, 'blob'))
            <a href="{{ $action_url }}" class="btn btn-primary">Download Failed Records</a>
        @else
            <a href="{{ $action_url }}" class="btn btn-primary">View Contacts</a>
        @endif
    </div>
    @endif
    
    <p>You can now start engaging with your imported contacts through conversations and campaigns.</p>
    
    <p>Best regards,<br>
    {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>
</div>
@endsection