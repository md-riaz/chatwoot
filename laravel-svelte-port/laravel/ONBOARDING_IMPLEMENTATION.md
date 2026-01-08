# Laravel Onboarding Implementation

This document describes the Laravel implementation of the first superadmin onboarding process, ensuring feature parity with the Rails backend.

## Overview

The Laravel API now includes a complete onboarding system that matches the Rails backend functionality for creating the first superadmin account with proper feature initialization.

## Key Components

### 1. ConfigLoaderService (`app/Services/ConfigLoaderService.php`)

Handles loading configuration from YAML files into the database, similar to Rails' `ConfigLoader`.

**Features:**
- Loads installation configuration from `config/installation_config.yml`
- Loads feature defaults from `config/features.yml`
- Supports reconciliation modes (new-only vs full update)
- Validates YAML configuration files
- Exports current configuration to YAML

**Usage:**
```php
$configLoader = new ConfigLoaderService([
    'reconcile_only_new' => true,
    'load_features' => true,
]);

$results = $configLoader->process();
```

### 2. AccountObserver (`app/Observers/AccountObserver.php`)

Automatically initializes default features when accounts are created, similar to Rails' `Featurable` concern.

**Features:**
- Triggers on account creation (`creating` event)
- Loads default features from `ACCOUNT_LEVEL_FEATURE_DEFAULTS` config
- Falls back to loading from YAML files if config missing
- Enables basic features as final fallback
- Maps YAML feature names to bit flag positions

### 3. Installation Commands

#### InitializeInstallationCommand (`app/Console/Commands/InitializeInstallationCommand.php`)

Initializes the installation configuration and optionally enables onboarding.

```bash
# Initialize configuration
php artisan installation:initialize

# Initialize and enable onboarding
php artisan installation:initialize --enable-onboarding

# Force reinitialize
php artisan installation:initialize --force
```

#### LoadConfigurationCommand (`app/Console/Commands/LoadConfigurationCommand.php`)

Loads configuration from YAML files (existing command, now functional).

```bash
# Load configuration
php artisan config:load

# Load only new configurations
php artisan config:load --reconcile-only-new

# Skip feature loading
php artisan config:load --no-features

# Validate before loading
php artisan config:load --validate
```

### 4. Onboarding Controllers

#### InstallationOnboardingController (`app/Http/Controllers/Api/V1/InstallationOnboardingController.php`)

**Updated Features:**
- Ensures configuration is loaded before account creation
- Returns enabled features in response
- Includes feature flags count in response

#### InstallationOnboardingStatusController (`app/Http/Controllers/Api/V1/InstallationOnboardingStatusController.php`)

Checks onboarding status via Redis flag.

### 5. Database Seeders

#### InstallationConfigSeeder (`database/seeders/InstallationConfigSeeder.php`)

Seeds installation configuration from YAML files during database setup.

#### Updated DatabaseSeeder

Now includes `InstallationConfigSeeder` in the seeding process.

## Configuration Files

### features.yml (`config/features.yml`)

Defines available features with their default enabled state:

```yaml
- name: email_integration
  display_name: Email Integration
  description: Handle customer emails as conversations
  enabled: true
  premium: false
  help_url: https://docs.chatwoot.com/integrations/email

- name: advanced_reporting
  display_name: Advanced Reporting
  description: Detailed analytics and custom reports
  enabled: false
  premium: true
  help_url: https://docs.chatwoot.com/features/reporting
```

### installation_config.yml (`config/installation_config.yml`)

Defines system-wide configuration options (unchanged from original).

## Feature Flag System

### Account Model Updates

The Account model already includes feature flag methods:
- `feature_enabled(string $feature): bool`
- `enableFeature(string $feature): bool`
- `disableFeature(string $feature): bool`
- `getEnabledFeatures(): array`

### Feature Flag Mapping

The AccountObserver includes a comprehensive mapping between YAML feature names and bit flag positions:

```php
'email_integration' => 1,
'website_widget' => 8388608,
'api_access' => 8192,
'team_management' => 1024,
// ... etc
```

## Onboarding Process Flow

1. **Initialization** (via seeder or command):
   - Load `installation_config.yml` into `InstallationConfig` table
   - Load `features.yml` and create `ACCOUNT_LEVEL_FEATURE_DEFAULTS` config
   - Set Redis onboarding flag

2. **Account Creation** (via onboarding endpoint):
   - Validate user input
   - Ensure configuration is loaded
   - Create account (triggers AccountObserver)
   - AccountObserver initializes default features
   - Create SuperAdmin user with confirmed email
   - Link user to account as administrator
   - Clear Redis onboarding flag

3. **Feature Initialization** (AccountObserver):
   - Load enabled features from `ACCOUNT_LEVEL_FEATURE_DEFAULTS`
   - Map feature names to bit flags
   - Set account's `feature_flags` field
   - Log enabled features

## API Endpoints

### GET /api/v1/installation/onboarding
Returns onboarding form data or error if already completed.

### POST /api/v1/installation/onboarding
Creates first superadmin account and company.

**Request:**
```json
{
  "user": {
    "name": "Super Admin",
    "company": "My Company",
    "email": "admin@company.com",
    "password": "securepassword"
  }
}
```

**Response:**
```json
{
  "message": "Super admin and account created successfully.",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@company.com",
    "type": "SuperAdmin"
  },
  "account": {
    "id": 1,
    "name": "My Company",
    "enabled_features": [
      "email_integration",
      "website_widget",
      "api_access",
      "team_management"
    ],
    "feature_flags": 8404992
  }
}
```

### GET /api/v1/installation/onboarding/status
Returns current onboarding status.

**Response:**
```json
{
  "onboarding_pending": true
}
```

## Testing

### SuperAdminOnboardingTest (`tests/Feature/Onboarding/SuperAdminOnboardingTest.php`)

Comprehensive test suite covering:
- Successful onboarding with feature initialization
- Validation errors
- Duplicate email prevention
- Onboarding flag management
- Account observer functionality
- Configuration loading
- Status endpoint

**Run tests:**
```bash
php artisan test tests/Feature/Onboarding/SuperAdminOnboardingTest.php
```

## Default Features Enabled

The following features are enabled by default for new accounts (matching Rails behavior):

**Core Features:**
- Email Integration
- Website Widget
- API Access
- Webhooks
- Team Management
- Automation Rules
- CSAT Surveys
- Campaigns

**Channel Integrations:**
- WhatsApp Integration
- Facebook Integration
- Instagram Integration
- Twitter Integration
- Slack Integration

**Conversation Management:**
- Macros
- Canned Responses
- Labels
- Contact Management
- Conversation Assignment
- Conversation Search
- File Attachments
- Conversation Notes
- Agent Availability
- Conversation Status
- Real-time Notifications

**Premium Features (Disabled by Default):**
- Advanced Reporting
- Audit Logs
- Custom Roles
- SLA Policies
- Linear Integration
- Shopify Integration
- OpenAI Integration

## Migration from Rails

This implementation ensures 100% feature parity with the Rails backend:

1. **Same Redis Key**: Uses `chatwoot_installation_onboarding`
2. **Same Feature System**: Bit flags with automatic initialization
3. **Same User Type**: Creates `SuperAdmin` type users
4. **Same Role Assignment**: Links as `administrator` role
5. **Same Configuration**: Loads from YAML files
6. **Same Default Features**: Enables identical feature set

## Deployment

### Development Setup

1. Run migrations:
```bash
php artisan migrate
```

2. Seed database (includes configuration loading):
```bash
php artisan db:seed
```

3. Initialize installation (if not using seeder):
```bash
php artisan installation:initialize --enable-onboarding
```

### Production Setup

1. Run migrations and seeders:
```bash
php artisan migrate --seed
```

2. The onboarding flag is automatically set by `OnboardingFlagSeeder`

3. Access `/installation/onboarding` to create first superadmin

### Configuration Updates

To update configuration after deployment:

```bash
# Load new configurations only
php artisan config:load --reconcile-only-new

# Force update all configurations
php artisan config:load

# Update specific installation
php artisan installation:initialize --force
```

## Troubleshooting

### No Features Enabled

If accounts are created without features:

1. Check if configuration is loaded:
```bash
php artisan tinker
>>> App\Models\InstallationConfig::where('name', 'ACCOUNT_LEVEL_FEATURE_DEFAULTS')->first()
```

2. Reload configuration:
```bash
php artisan config:load
```

3. Check AccountObserver is registered in `AppServiceProvider`

### Onboarding Not Available

1. Check Redis flag:
```bash
php artisan tinker
>>> Redis::get('chatwoot_installation_onboarding')
```

2. Reset onboarding:
```bash
php artisan installation:initialize --enable-onboarding
```

### Configuration Not Loading

1. Verify YAML files exist:
   - `config/features.yml`
   - `config/installation_config.yml`

2. Check YAML syntax:
```bash
php artisan config:load --validate
```

3. Check file permissions and paths

## Conclusion

The Laravel implementation now provides complete feature parity with the Rails backend for superadmin onboarding, including:

- ✅ Automatic feature flag initialization
- ✅ Configuration loading from YAML files
- ✅ Redis-based onboarding control
- ✅ SuperAdmin user creation
- ✅ Account-user linking with proper roles
- ✅ Comprehensive test coverage
- ✅ Console commands for management
- ✅ Proper error handling and logging

The system is production-ready and maintains compatibility with the existing Rails patterns while leveraging Laravel's conventions and best practices.