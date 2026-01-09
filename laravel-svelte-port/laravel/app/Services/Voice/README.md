# Voice Services - Laravel Implementation

This directory contains the Laravel implementation of Chatwoot's Voice functionality, providing 100% Rails parity for Twilio Voice integration.

## Architecture Overview

The Voice services follow Laravel-native patterns while maintaining functional parity with the Rails backend:

```
Voice/
├── Provider/
│   └── Twilio/
│       ├── AdapterService.php      # Call initiation and management
│       ├── ConferenceService.php   # Conference management
│       ├── TokenService.php        # WebRTC token generation
│       └── WebhookSetupService.php # Twilio webhook provisioning
├── CallStatus/
│   └── ManagerService.php          # Call status updates
├── Conference/
│   └── ManagerService.php          # Conference event handling
└── README.md                       # This file
```

## Rails Parity Features

### ✅ Complete Twilio Integration
- **WebhookSetupService**: Automatic TwiML app creation and webhook configuration
- **AdapterService**: Call initiation with proper status callbacks
- **TokenService**: WebRTC access token generation for browser-based calling
- **ConferenceService**: Conference management and participant handling

### ✅ Call Management
- **Inbound Calls**: Automatic conversation creation and contact resolution
- **Outbound Calls**: Agent-initiated calls with conference setup
- **Call Status Tracking**: Real-time status updates (ringing, in-progress, completed, failed)
- **Conference Events**: Join/leave tracking and proper call termination

### ✅ Laravel-Native Implementation
- **Service Classes**: Clean separation of concerns following Laravel patterns
- **Action Classes**: Business logic encapsulation using lorisleiva/laravel-actions
- **Factory Pattern**: Consistent object creation with demo data support
- **Event-Driven**: Proper model events for automatic Twilio provisioning

## Usage Examples

### Creating a Voice Channel
```php
$voice = Voice::create([
    'account_id' => $account->id,
    'phone_number' => '+15551234567',
    'provider' => 'twilio',
    'provider_config' => [
        'account_sid' => 'AC...',
        'auth_token' => 'auth_token',
        'api_key_sid' => 'SK...',
        'api_key_secret' => 'secret',
    ],
]);
// Automatically provisions TwiML app and configures webhooks
```

### Initiating an Outbound Call
```php
$result = InitiateOutboundCallAction::run($account, $inbox, $user, $contact);
// Returns: ['conversation' => $conversation, 'call_sid' => $callSid, 'conference_sid' => $conferenceSid]
```

### Generating WebRTC Token
```php
$tokenService = new TokenService($inbox, $user, $account);
$tokenData = $tokenService->generate();
// Returns token for browser-based calling
```

## Rails Compatibility

This implementation maintains 100% functional parity with Rails:

| Rails Component | Laravel Equivalent | Status |
|---|---|---|
| `Channel::Voice` | `App\Models\Channels\Voice` | ✅ Complete |
| `Voice::Provider::Twilio::Adapter` | `AdapterService` | ✅ Complete |
| `Twilio::VoiceWebhookSetupService` | `WebhookSetupService` | ✅ Complete |
| `Voice::Provider::Twilio::TokenService` | `TokenService` | ✅ Complete |
| `Voice::Conference::Manager` | `Conference\ManagerService` | ✅ Complete |
| `Voice::CallStatus::Manager` | `CallStatus\ManagerService` | ✅ Complete |
| `Voice::InboundCallBuilder` | `HandleInboundCallAction` | ✅ Complete |
| `Voice::OutboundCallBuilder` | `InitiateOutboundCallAction` | ✅ Complete |

## Key Differences from Rails

While maintaining functional parity, the Laravel implementation uses Laravel-native approaches:

1. **Service Classes** instead of Rails service objects
2. **Action Classes** instead of Rails builders
3. **Model Events** instead of Rails callbacks
4. **Factory Pattern** for consistent object creation
5. **Laravel Validation** instead of Rails validations

## Testing

Comprehensive test coverage includes:
- Voice channel creation and validation
- Twilio integration (mocked)
- Call flow testing (inbound/outbound)
- Conference management
- Webhook handling

Run tests:
```bash
php artisan test tests/Feature/Voice/
```

## Configuration

Voice channels require Twilio credentials:
```env
# Twilio credentials are stored per-channel in provider_config
# No global configuration needed
```

## Webhook Endpoints

The following webhook endpoints are automatically configured:
- `POST /api/v1/webhooks/voice/call/{phone}` - TwiML generation
- `POST /api/v1/webhooks/voice/status/{phone}` - Call status updates  
- `POST /api/v1/webhooks/voice/conference_status/{phone}` - Conference events

## Production Considerations

1. **Twilio Credentials**: Store securely in channel configuration
2. **Webhook URLs**: Must be publicly accessible HTTPS endpoints
3. **Phone Number Validation**: E.164 format required
4. **Error Handling**: Comprehensive logging for Twilio API errors
5. **Rate Limiting**: Consider Twilio API rate limits for high-volume usage

This implementation provides enterprise-grade voice functionality with complete Rails parity while leveraging Laravel's strengths.