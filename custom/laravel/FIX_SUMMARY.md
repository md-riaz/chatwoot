# SQL Insert Error Fix - Summary

## Issue
The Laravel application was failing with the following PostgreSQL error when creating accounts:
```
SQLSTATE[22P02]: Invalid text representation: 7 ERROR:  
invalid input syntax for type integer: "en"
```

## Root Cause
The `accounts` table has `locale` as an `integer` column (matching the Rails backend enum implementation), but the Laravel code was attempting to insert string values like "en", "fr", etc. directly into the database without proper conversion.

## Solution Overview
Created a `Locale` enum that provides seamless conversion between locale codes (strings) and their integer database representations, maintaining full compatibility with the Rails backend.

## Changes Made

### 1. Core Files
- **`app/Enums/Locale.php`** (NEW)
  - Enum with 41 locale cases matching Rails LANGUAGES_CONFIG
  - Maps locale codes to integers (en→0, ar→1, fr→3, etc.)
  - Provides `getCode()` and `fromCode()` methods for conversion

- **`app/Models/Account.php`** (MODIFIED)
  - Added `'locale' => Locale::class` to $casts
  - Added `setLocaleAttribute()` mutator for automatic string→integer conversion
  - Added `getLocaleCodeAttribute()` accessor for backwards compatibility

### 2. API Resources
- **`app/Http/Resources/Account/AccountResource.php`** (MODIFIED)
  - Returns locale as string code for API responses
  
- **`app/Http/Resources/SuperAdmin/AccountResource.php`** (MODIFIED)
  - Returns locale as string code for API responses

### 3. Tests
- **`tests/Unit/Enums/LocaleEnumTest.php`** (NEW)
  - Tests enum functionality, code conversion, and value mapping
  
- **`tests/Unit/Models/AccountLocaleTest.php`** (NEW)
  - Tests Account model locale handling, database operations, and accessors

### 4. Documentation
- **`ACCOUNT_LOCALE_FIX.md`** (NEW)
  - Comprehensive guide on the fix implementation
  
- **`LOCALE_FIX_VERIFICATION.md`** (NEW)
  - Before/after scenarios and flow diagrams

## Key Features

### Automatic Conversion
```php
// String input automatically converted to integer
Account::create(['name' => 'Test', 'locale' => 'en']);
// Database stores: locale = 0

// Reading returns enum, convertible to string
$account->locale->getCode(); // 'en'
$account->locale_code; // 'en' (accessor)
```

### API Compatibility
```json
// Request
POST /api/v1/accounts
{
  "name": "My Company",
  "locale": "en"
}

// Response
{
  "id": 1,
  "name": "My Company",
  "locale": "en"
}
```

### Rails Compatibility
The integer mappings exactly match Rails LANGUAGES_CONFIG, ensuring data compatibility:
- Both systems store locale as integer in database
- Both use the same integer values (en=0, ar=1, fr=3, etc.)
- Data can be shared between Rails and Laravel implementations

## Testing Recommendations

### 1. Manual Testing
```bash
# Create account via API
curl -X POST http://localhost:8000/api/v1/accounts \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Company","locale":"en","status":1}'

# Verify in database
psql -d chatwoot_development -c "SELECT id, name, locale FROM accounts ORDER BY id DESC LIMIT 1;"
# Should show: locale = 0
```

### 2. Unit Tests
```bash
cd custom/laravel
php artisan test --filter=LocaleEnumTest
php artisan test --filter=AccountLocaleTest
```

### 3. Integration Tests
```bash
# Test account creation through full stack
php artisan test --filter=AccountsCrudTest
```

## Rollback Plan
If issues arise, revert with:
```bash
git revert 55de0e4  # Verification docs
git revert ff22ba7  # API resources update
git revert fe16195  # Core enum and model changes
```

Note: Only revert if critical issues found. The changes are designed to be backwards compatible.

## Migration Notes
- No database migrations needed (schema already correct)
- Existing data unaffected (integers remain integers)
- New accounts will automatically use correct conversion

## Files Changed
1. `app/Enums/Locale.php` - NEW
2. `app/Models/Account.php` - MODIFIED
3. `app/Http/Resources/Account/AccountResource.php` - MODIFIED
4. `app/Http/Resources/SuperAdmin/AccountResource.php` - MODIFIED
5. `tests/Unit/Enums/LocaleEnumTest.php` - NEW
6. `tests/Unit/Models/AccountLocaleTest.php` - NEW
7. `ACCOUNT_LOCALE_FIX.md` - NEW
8. `LOCALE_FIX_VERIFICATION.md` - NEW

## Status
✅ Code complete
✅ PHP syntax verified
✅ Documentation complete
⏳ Awaiting testing (requires Laravel environment setup)
