# Contact City Duplication Fix

## Problem
The frontend was sending city data in both root level and `additional_attributes`, causing confusion and duplication. The Laravel API wasn't handling the Rails-compatible data structure properly.

## Root Cause
1. **Database Schema Mismatch**: Laravel had both `city` and `location` fields, but Rails only uses `location`
2. **Missing Sync Logic**: Laravel lacked the `SyncAttributes` service that Rails uses
3. **Incomplete API Response**: Laravel's `ContactResource` didn't return Rails-compatible fields
4. **Missing Validation**: Laravel's request validation didn't handle `additional_attributes` structure

## Solution
1. **Removed city field** from Contact model fillable array (following Rails pattern)
2. **Created ContactSyncAttributesService** to sync `additional_attributes['city']` → `location` field
3. **Added ContactObserver** to automatically call sync service on save
4. **Updated ContactResource** to return Rails-compatible response with `thumbnail` and Unix timestamps
5. **Enhanced validation** to validate `additional_attributes.city` and `additional_attributes.country_code`
6. **Fixed controller** to merge attributes instead of replacing them

## Rails Compatibility
- City stored in `additional_attributes['city']` and synced to `location` field
- Country stored in `additional_attributes['country_code']` and synced to `country_code` field
- API returns `thumbnail` instead of `avatar_url`
- Timestamps returned as Unix timestamps, not ISO strings
- Contact type automatically promoted from visitor to lead when email/phone/social details present

## Files Changed
- `app/Models/Contact.php` - Removed city from fillable, added default values
- `app/Http/Resources/Contact/ContactResource.php` - Rails-compatible response format
- `app/Http/Requests/Contact/StoreContactRequest.php` - Added additional_attributes validation
- `app/Data/Contact/ContactData.php` - Removed city field, Rails pattern
- `app/Services/Contact/ContactSyncAttributesService.php` - NEW: Sync service
- `app/Observers/ContactObserver.php` - NEW: Auto-sync on save
- `app/Providers/EventServiceProvider.php` - Register ContactObserver
- `app/Http/Controllers/Api/V1/ContactsController.php` - Merge attributes logic

## Testing
- `tests/Feature/Contact/ContactSyncAttributesTest.php` - Tests sync service
- `tests/Feature/Contact/ContactApiTest.php` - Tests API endpoints

The fix ensures the frontend can send city in `additional_attributes` and it will be properly synced to the `location` field, matching Rails behavior exactly.