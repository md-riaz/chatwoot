# Account Locale Fix

## Problem
The error occurred because the `accounts` table has `locale` as an `integer` column (matching Rails enum implementation), but the Laravel code was trying to insert string values like "en", "fr", etc. directly into the database.

## Solution
Created a `Locale` enum that maps locale codes (strings) to integer values, matching the Rails `LANGUAGES_CONFIG` implementation.

### Changes Made

1. **Created `app/Enums/Locale.php`**
   - Enum with cases for all 41 supported locales
   - Maps locale codes (en, ar, fr, etc.) to integers (0, 1, 3, etc.)
   - Provides `getCode()` method to get string code from enum
   - Provides `fromCode()` method to create enum from string code

2. **Updated `app/Models/Account.php`**
   - Added `'locale' => Locale::class` to `$casts` array
   - Added mutator `setLocaleAttribute()` that converts string/enum/int to integer for storage
   - Added accessor `getLocaleCodeAttribute()` for backwards compatibility with code expecting locale as string

3. **Updated API Resources**
   - `app/Http/Resources/Account/AccountResource.php`: Returns locale as string code
   - `app/Http/Resources/SuperAdmin/AccountResource.php`: Returns locale as string code

4. **Created Tests**
   - `tests/Unit/Enums/LocaleEnumTest.php`: Tests enum functionality
   - `tests/Unit/Models/AccountLocaleTest.php`: Tests Account locale handling

## Usage

### Creating an Account
```php
// Using string locale code (recommended)
$account = Account::create([
    'name' => 'My Company',
    'locale' => 'en',  // Will be converted to 0
    'status' => 1,
]);

// Using enum directly
$account = Account::create([
    'name' => 'My Company',
    'locale' => Locale::FR,  // Will be stored as 3
    'status' => 1,
]);
```

### Reading Locale
```php
$account = Account::find(1);

// As enum (for comparisons)
if ($account->locale === Locale::EN) {
    // ...
}

// As string code (for display/API)
$localeCode = $account->locale->getCode(); // 'en'

// Or using the accessor
$localeCode = $account->locale_code; // 'en'
```

### API Responses
The API automatically returns locale as a string code:
```json
{
    "id": 1,
    "name": "My Company",
    "locale": "en",
    ...
}
```

## Database Schema
```sql
-- accounts table
CREATE TABLE accounts (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    locale INTEGER DEFAULT 0,  -- 0=en, 1=ar, 3=fr, etc.
    ...
);
```

## Mapping Reference
- 0 = en (English)
- 1 = ar (Arabic)
- 2 = nl (Dutch)
- 3 = fr (French)
- 4 = de (German)
- ... (see `app/Enums/Locale.php` for complete mapping)

## Backwards Compatibility
The changes are backwards compatible:
- Existing code can continue to pass locale as a string
- The mutator automatically converts it to the correct integer value
- API responses return locale as a string code
- The `locale_code` accessor provides easy access to the string representation
