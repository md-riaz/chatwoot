<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notification') - {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .email-header {
            background-color: #1f93ff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .email-content {
            padding: 30px;
        }
        
        .email-content h2 {
            color: #1f93ff;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .email-content p {
            margin-bottom: 16px;
        }
        
        .conversation-details,
        .sla-details,
        .conversation-summary,
        .mention-message,
        .new-message,
        .latest-message {
            background-color: #f8f9fa;
            border-left: 4px solid #1f93ff;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .conversation-details h3,
        .sla-details h3,
        .conversation-summary h3,
        .mention-message h3,
        .new-message h3,
        .latest-message h3 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #1f93ff;
            font-size: 16px;
            font-weight: 600;
        }
        
        .conversation-details ul,
        .sla-details ul,
        .conversation-summary ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .conversation-details li,
        .sla-details li,
        .conversation-summary li {
            margin-bottom: 8px;
        }
        
        .message-content {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 12px;
            margin-top: 8px;
        }
        
        .message-sender {
            font-weight: 600;
            margin-bottom: 8px;
            color: #1f93ff;
        }
        
        .message-time {
            font-weight: normal;
            color: #6c757d;
            font-size: 14px;
        }
        
        .message-text {
            color: #333;
            line-height: 1.5;
        }
        
        .action-button {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: #1f93ff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d7ae4;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        
        .email-footer a {
            color: #1f93ff;
            text-decoration: none;
        }
        
        .email-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-content {
                padding: 20px;
            }
            
            .email-header {
                padding: 15px;
            }
            
            .email-header h1 {
                font-size: 20px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }}</h1>
        </div>
        
        @yield('content')
        
        <div class="email-footer">
            <p>
                This email was sent by {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }}.
                @if($global_config['BRAND_URL'] ?? config('app.url'))
                    <br><a href="{{ $global_config['BRAND_URL'] ?? config('app.url') }}">Visit our website</a>
                @endif
            </p>
            <p>
                <small>
                    If you no longer wish to receive these notifications, 
                    please update your notification preferences in your account settings.
                </small>
            </p>
        </div>
    </div>
</body>
</html>