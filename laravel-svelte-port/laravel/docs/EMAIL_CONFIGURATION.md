# Email System Configuration

This document explains how to configure the dynamic email system in the Laravel application.

## Overview

The email system uses a hierarchical configuration approach:

1. **Global Configuration** (Database) - Highest priority
2. **Email Configuration** (`config/email.php`) - Medium priority  
3. **App Configuration** (`config/app.php`) - Lowest priority

## Configuration Hierarchy

### Brand Configuration

The system will use brand information in this order:

1. `global_config['BRAND_NAME']` from database
2. `config('email.brand.name')` from email config
3. `config('app.name')` from app config
4. `'Chatwoot'` as final fallback

### URL Configuration

URLs are resolved in this order:

1. `global_config['BRAND_URL']` from database
2. `config('email.brand.url')` from email config
3. `config('app.url')` from app config

## Environment Variables

### Basic Branding
```env
APP_NAME="Your Company Name"
APP_URL=https://yourcompany.com
```

### Email-Specific Branding
```env
EMAIL_BRAND_NAME="Your Support Team"
EMAIL_BRAND_URL=https://support.yourcompany.com
EMAIL_BRAND_LOGO_URL=https://yourcompany.com/logo.png
EMAIL_SUPPORT_EMAIL=support@yourcompany.com
```

### Domain Configuration
```env
EMAIL_PORTAL_DOMAIN=help.yourcompany.com
EMAIL_REPLY_DOMAIN=yourcompany.com
```

### Template Settings
```env
EMAIL_TEMPLATE_CACHE_TTL=3600
EMAIL_LIQUID_ENABLED=true
EMAIL_FALLBACK_LOCALE=en
```

### Bounce Handling
```env
EMAIL_MAX_SOFT_BOUNCES=5
EMAIL_SOFT_BOUNCE_RESET_DAYS=30
EMAIL_AUTO_DISABLE_HARD_BOUNCE=true
EMAIL_AUTO_DISABLE_COMPLAINT=true
```

## Template Variables

All email templates have access to these dynamic variables:

### Global Variables
- `{{ $global_config['BRAND_NAME'] }}` - Brand name
- `{{ $global_config['BRAND_URL'] }}` - Brand URL
- `{{ config('app.name') }}` - Application name
- `{{ config('app.url') }}` - Application URL

### Template Usage Examples

```blade
<!-- Dynamic brand name in subject -->
<title>Notification - {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }}</title>

<!-- Dynamic signature -->
<p>Best regards,<br>
{{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }} Team</p>

<!-- Dynamic footer -->
<p>This email was sent by {{ $global_config['BRAND_NAME'] ?? config('app.name', 'Chatwoot') }}.</p>
```

## Customization Examples

### Multi-Brand Setup

For different brands per account:

1. Set global configuration in database:
```php
GlobalConfig::set('BRAND_NAME', 'Brand A');
GlobalConfig::set('BRAND_URL', 'https://branda.com');
```

2. Or use account-specific email templates in database

### White-Label Setup

For white-label installations:

```env
APP_NAME="Client Company"
APP_URL=https://clientcompany.com
EMAIL_BRAND_NAME="Client Support"
EMAIL_SUPPORT_EMAIL=support@clientcompany.com
EMAIL_PORTAL_DOMAIN=help.clientcompany.com
```

### Development Setup

For development environments:

```env
APP_NAME="MyApp Dev"
APP_URL=http://localhost:8000
EMAIL_BRAND_NAME="MyApp Development"
EMAIL_SUPPORT_EMAIL=dev@localhost
```

## Template Customization

### File-Based Templates

Templates are located in `resources/views/emails/` and automatically use dynamic configuration.

### Database Templates

You can override templates per account or globally:

```php
EmailTemplate::create([
    'account_id' => $account->id, // null for global
    'name' => 'conversation_creation',
    'locale' => 'en',
    'body' => 'Custom template content with {{ $global_config["BRAND_NAME"] }}'
]);
```

### Liquid Templates

If Liquid templates are enabled (`EMAIL_LIQUID_ENABLED=true`), you can use Liquid syntax:

```liquid
Hello {{ user.name }},

A new conversation has been created in {{ global_config.BRAND_NAME }}.

Best regards,
{{ global_config.BRAND_NAME }} Team
```

## Testing Configuration

To test your email configuration:

```php
// Test brand configuration
$config = app(GlobalConfigService::class)->get(['BRAND_NAME', 'BRAND_URL']);
dd($config);

// Test email sending
Mail::to('test@example.com')->send(
    ConversationNotificationMail::conversationCreation($conversation, $user)
);
```

## Troubleshooting

### Brand Name Not Updating

1. Check global configuration in database
2. Clear template cache: `php artisan cache:clear`
3. Verify environment variables are loaded

### Templates Not Found

1. Check template exists in `resources/views/emails/`
2. Verify template name matches mailer configuration
3. Check template cache TTL setting

### Configuration Priority Issues

Remember the hierarchy:
1. Database global config (highest)
2. Email config file
3. App config file (lowest)

Use `config:cache` and `config:clear` to refresh configuration cache.