# Super Admin Accounts Implementation

This document describes the refactored implementation of the Super Admin Accounts list feature following Laravel best practices.

## Architecture Overview

The implementation follows the Action → Repository → Model pattern as defined in `AGENTS.md`:

```
Controller → Action → Repository → Model
                ↓
              DTO (Data Transfer Object)
```

## Components

### 1. Data Transfer Objects (DTOs)

Located in `app/Data/SuperAdmin/`:

- **AccountData.php** - Represents a single account with validation rules
  - Handles conversion between API requests/responses and internal format
  - Validates input using Spatie Data attributes
  - Converts status between string ('active'/'suspended') and integer (0/1)

- **AccountsListData.php** - Wraps paginated accounts response
  - Contains array of AccountData and metadata

- **AccountsListMetaData.php** - Pagination metadata
  - total, per_page, current_page, last_page

### 2. Repository

Located in `app/Repositories/SuperAdmin/AccountRepository.php`:

- Extends BaseRepository for common CRUD operations
- **getPaginated()** - Handles all filtering and pagination logic
  - Search (name, domain)
  - Status filter (active/suspended)
  - Recent filter (30 days)
  - Marked for deletion filter
  - Loads counts: users, inboxes, conversations

- **getWithDetails()** - Loads account with all relationships for show page
  - Includes counts and limited users (10)

### 3. Actions

Located in `app/Actions/SuperAdmin/`:

- **ListAccountsAction.php** - Orchestrates account listing
  - Calls repository for paginated data
  - Transforms models to DTOs
  - Returns AccountsListData

- **GetAccountAction.php** - Retrieves single account
  - Calls repository with relationships
  - Transforms to DTO

- **CreateAccountAction.php** - Creates new account
  - Validates via DTO
  - Persists to database
  - Returns created account as DTO

- **UpdateAccountAction.php** - Updates existing account
  - Validates via DTO
  - Updates database
  - Returns updated account as DTO

### 4. Controller

Located in `app/Http/Controllers/Api/V1/SuperAdmin/AccountsController.php`:

- Uses constructor dependency injection for Actions
- Thin controller that delegates to Actions
- Handles HTTP request/response mapping
- Returns JSON responses with proper status codes

```php
public function __construct(
    private ListAccountsAction $listAccounts,
    private GetAccountAction $getAccount,
    private CreateAccountAction $createAccount,
    private UpdateAccountAction $updateAccount
) {}
```

## Request/Response Format

### List Accounts

**Request:**
```
GET /api/v1/super_admin/accounts?page=1&per_page=20&search=&status=active
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Account Name",
      "domain": "account.example.com",
      "status": "active",
      "locale": "en",
      "support_email": "support@example.com",
      "auto_resolve_duration": null,
      "settings": {},
      "limits": {},
      "custom_attributes": {},
      "users_count": 5,
      "inboxes_count": 3,
      "conversations_count": 42,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "total": 10,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

### Get Single Account

**Request:**
```
GET /api/v1/super_admin/accounts/1
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Account Name",
    ...
  }
}
```

### Create Account

**Request:**
```
POST /api/v1/super_admin/accounts
Content-Type: application/json

{
  "name": "New Account",
  "locale": "en",
  "domain": "new.example.com",
  "support_email": "support@new.example.com"
}
```

**Response:** (201 Created)
```json
{
  "data": { ... }
}
```

### Update Account

**Request:**
```
PUT /api/v1/super_admin/accounts/1
Content-Type: application/json

{
  "name": "Updated Name",
  "status": "suspended"
}
```

**Response:**
```json
{
  "data": { ... }
}
```

## Filters

The list endpoint supports the following filters:

- **search** - Search by name or domain
- **status** - Filter by status ('active' or 'suspended')
- **recent** - Filter accounts created in last 30 days (boolean)
- **marked_for_deletion** - Filter accounts marked for deletion (boolean)
- **page** - Pagination page number (default: 1)
- **per_page** - Items per page (default: 20)

## Parity with Rails

This implementation maintains feature parity with the Rails backend:

| Feature | Rails | Laravel |
|---------|-------|---------|
| Search | Administrate search | Query params |
| Filters | Collection filters | Query params |
| Pagination | Administrate default | Configurable (20 default) |
| Counts | Displayed in list | Included in response |
| Status | active/suspended | String enum |
| Deletion | Async job | Soft delete |
| Cache management | Rails cache | Laravel Cache facade |

## Frontend Integration

The Svelte-UI component (`custom/ui/svelte-ui/src/routes/app/super_admin/accounts/+page.svelte`) consumes this API:

- Calls `superAdminApi.getAccounts()` with filter parameters
- Displays accounts in DataTable with columns: ID, Name, Users, Conversations, Created At, Status
- Supports search, status filtering, and pagination
- Navigates to detail page on row click

## Testing

To test the implementation:

```bash
# Run Laravel tests
php artisan test

# Run specific test
php artisan test tests/Feature/SuperAdmin/AccountsControllerTest.php
```

## Future Enhancements

- Add seed action for demo data
- Implement async account deletion job
- Add audit logging for account changes
- Add bulk operations (delete, status change)
