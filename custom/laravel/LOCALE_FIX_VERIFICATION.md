# Verification of Account Locale Fix

This document demonstrates how the locale fix resolves the SQL error.

## Original Error
```
SQLSTATE[22P02]: Invalid text representation: 7 ERROR:  
invalid input syntax for type integer: "en"
CONTEXT:  unnamed portal parameter $2 = '...' 
(Connection: pgsql, SQL: insert into "accounts" 
(name, locale, domain, support_email, status, updated_at, created_at) 
values (Alpha Net, en, ?, ?, 1, 2026-01-04 06:45:55, 2026-01-04 06:45:55) 
returning "id")
```

The error occurred because:
1. The `accounts.locale` column is of type `integer` (matching Rails schema)
2. The code was trying to insert the string `"en"` directly
3. PostgreSQL rejected the insert due to type mismatch

## The Fix

### Before (Broken)
```php
// AccountData expects locale as string
$data = [
    'name' => 'Alpha Net',
    'locale' => 'en',  // String value
    'status' => 1,
];

// Account model tries to insert directly
Account::create($data);
// SQL: INSERT INTO accounts (name, locale, ...) VALUES ('Alpha Net', 'en', ...)
// ERROR: invalid input syntax for type integer: "en"
```

### After (Fixed)
```php
// AccountData still expects locale as string (API compatibility)
$data = [
    'name' => 'Alpha Net',
    'locale' => 'en',  // String value
    'status' => 1,
];

// Account model's mutator converts string to integer
Account::create($data);
// The setLocaleAttribute mutator is called:
//   'en' -> Locale::fromCode('en') -> Locale::EN -> value = 0
// SQL: INSERT INTO accounts (name, locale, ...) VALUES ('Alpha Net', 0, ...)
// SUCCESS: Integer value 0 is inserted
```

## Flow Diagram

```
API Request (locale: "en")
    ↓
AccountData validates (expects string)
    ↓
Account::create(['locale' => 'en'])
    ↓
setLocaleAttribute() mutator
    ↓
Locale::fromCode('en')
    ↓
Locale::EN (enum case)
    ↓
Locale::EN->value = 0 (integer)
    ↓
Database INSERT (locale = 0)
    ↓
SUCCESS
```

## Reading Flow

```
Database (locale = 0)
    ↓
Account::find(1)
    ↓
Cast to Locale enum (via $casts)
    ↓
$account->locale = Locale::EN
    ↓
API Resource: $account->locale->getCode()
    ↓
API Response (locale: "en")
```

## Test Cases

### Test 1: Create account with 'en'
```php
$account = Account::create([
    'name' => 'Test Company',
    'locale' => 'en',
    'status' => 1,
]);
// Database: locale = 0
// Read: $account->locale->getCode() = 'en' ✓
```

### Test 2: Create account with 'fr'
```php
$account = Account::create([
    'name' => 'Test Company',
    'locale' => 'fr',
    'status' => 1,
]);
// Database: locale = 3
// Read: $account->locale->getCode() = 'fr' ✓
```

### Test 3: Create account with 'es'
```php
$account = Account::create([
    'name' => 'Test Company',
    'locale' => 'es',
    'status' => 1,
]);
// Database: locale = 12
// Read: $account->locale->getCode() = 'es' ✓
```

## Compatibility

### With Rails Backend
The integer values match exactly with Rails `LANGUAGES_CONFIG`:
- Rails: `enum :locale, { en: 0, ar: 1, nl: 2, fr: 3, ... }`
- Laravel: `enum Locale: int { case EN = 0; case AR = 1; ... }`

Both systems store the same integer values in the database, ensuring data compatibility.

### API Compatibility
API requests and responses use locale codes as strings:
```json
{
  "name": "My Company",
  "locale": "en"
}
```

The enum conversion happens transparently at the model layer.
