<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Channel Reauthorization Required</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Action Required: {{ $channelName }} Channel Needs Reauthorization</h1>
    </div>

    <div class="alert">
        <strong>⚠️ Immediate Action Required</strong><br>
        Your {{ $channelName }} channel has encountered authentication errors and requires reauthorization to continue functioning.
    </div>

    <p>Hello {{ $user->name }},</p>

    <p>We're writing to inform you that your <strong>{{ $channelName }}</strong> channel in the <strong>{{ $accountName }}</strong> account has encountered multiple authentication errors and requires immediate reauthorization.</p>

    <h3>What this means:</h3>
    <ul>
        <li>Your {{ $channelName }} channel is currently inactive</li>
        <li>New messages from customers will not be received</li>
        <li>Outgoing messages cannot be sent</li>
        <li>This may be due to expired tokens, changed passwords, or revoked permissions</li>
    </ul>

    <h3>What you need to do:</h3>
    <ol>
        <li>Log into your Chatwoot dashboard</li>
        <li>Navigate to Settings → Inboxes</li>
        <li>Find your {{ $channelName }} channel</li>
        <li>Click "Reconnect" or "Reauthorize"</li>
        <li>Follow the authentication flow to restore the connection</li>
    </ol>

    <a href="{{ config('app.url') }}/app/accounts/{{ $channel->account_id }}/settings/inboxes" class="button">
        Reauthorize Channel Now
    </a>

    <h3>Channel Details:</h3>
    <ul>
        <li><strong>Channel Type:</strong> {{ $channelName }}</li>
        <li><strong>Account:</strong> {{ $accountName }}</li>
        @if(isset($channel->phone_number))
        <li><strong>Phone Number:</strong> {{ $channel->phone_number }}</li>
        @endif
        @if(isset($channel->page_id))
        <li><strong>Page ID:</strong> {{ $channel->page_id }}</li>
        @endif
    </ul>

    <p><strong>Time-sensitive:</strong> Please reauthorize your channel as soon as possible to avoid missing customer messages and maintain service quality.</p>

    <p>If you continue to experience issues after reauthorization, please contact our support team.</p>

    <div class="footer">
        <p>Best regards,<br>The Chatwoot Team</p>
        <p><small>This is an automated notification. If you believe you received this email in error, please contact support.</small></p>
    </div>
</body>
</html>