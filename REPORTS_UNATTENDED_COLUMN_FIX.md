# Reports API - Unattended Column Fix

## Issue

Database errors when fetching live reports:
```
SQLSTATE[42703]: Undefined column: 7 ERROR: column "unattended" does not exist
```

The `conversations` table doesn't have an `unattended` column.

## Root Cause

The `LiveReportsRepository` was trying to query a non-existent `unattended` column:

```php
// ❌ WRONG - column doesn't exist
->where('unattended', true)
```

## Rails Implementation

In Rails/Chatwoot, `unattended` is not a database column but a **scope** that calculates the value:

```ruby
# app/models/conversation.rb
scope :unattended, -> { 
  where(first_reply_created_at: nil).or(where.not(waiting_since: nil)) 
}
```

**Unattended means**:
- No agent has replied yet (`first_reply_created_at` is null), **OR**
- Conversation is waiting for agent response (`waiting_since` is not null)

## Fix Applied

### 1. Added Scope to Conversation Model ✅

**File**: `laravel-svelte-port/laravel/app/Models/Conversation.php`

```php
/**
 * Scope a query to only include unattended conversations.
 * Rails parity: scope :unattended, -> { where(first_reply_created_at: nil).or(where.not(waiting_since: nil)) }
 * 
 * Unattended means:
 * - No agent has replied yet (first_reply_created_at is null), OR
 * - Conversation is waiting for agent response (waiting_since is not null)
 */
public function scopeUnattended($query)
{
    return $query->where(function ($q) {
        $q->whereNull('first_reply_created_at')
          ->orWhereNotNull('waiting_since');
    });
}
```

### 2. Updated LiveReportsRepository ✅

**File**: `laravel-svelte-port/laravel/app/Repositories/Reports/LiveReportsRepository.php`

**Before (Broken)**:
```php
'unattended' => (clone $query)->where('status', Conversation::STATUS_OPEN)
    ->where('unattended', true)->count(),  // ❌ Column doesn't exist
```

**After (Fixed)**:
```php
'unattended' => (clone $query)->where('status', Conversation::STATUS_OPEN)
    ->unattended()->count(),  // ✅ Uses scope
```

## Database Schema

The `conversations` table has these relevant columns:

```php
$table->timestamp('first_reply_created_at')->nullable();
$table->timestamp('waiting_since')->nullable();
```

**No `unattended` column** - it's calculated dynamically using these two fields.

## Logic Explanation

### Unattended Calculation

A conversation is considered "unattended" when:

1. **No agent reply yet**: `first_reply_created_at IS NULL`
   - Customer sent a message
   - No agent has responded yet
   - Needs immediate attention

2. **Waiting for agent**: `waiting_since IS NOT NULL`
   - Customer sent a new message after agent's last reply
   - Agent needs to respond again
   - Conversation is "waiting" for agent action

### SQL Query

```sql
SELECT COUNT(*) 
FROM conversations 
WHERE account_id = 1 
  AND status = 0  -- open
  AND (
    first_reply_created_at IS NULL 
    OR waiting_since IS NOT NULL
  )
```

## Usage Examples

### Account Metrics
```php
$metrics = $repository->getAccountMetrics(accountId: 1);
// Returns: ['open' => 42, 'unattended' => 5, 'unassigned' => 3, 'pending' => 0]
```

### Grouped Metrics (by team)
```php
$metrics = $repository->getGroupedMetrics(accountId: 1, groupBy: 'team_id');
// Returns: [
//   ['team_id' => 1, 'open' => 10, 'unattended' => 2, 'unassigned' => 1],
//   ['team_id' => 2, 'open' => 15, 'unattended' => 3, 'unassigned' => 2],
// ]
```

### Using the Scope Directly
```php
// Get all unattended conversations
$unattended = Conversation::where('account_id', 1)
    ->open()
    ->unattended()
    ->get();

// Count unattended conversations for a team
$count = Conversation::where('account_id', 1)
    ->where('team_id', 5)
    ->open()
    ->unattended()
    ->count();
```

## Testing

The API endpoints should now work correctly:

```bash
# Test account metrics
curl http://localhost:8000/api/v1/accounts/1/v2/live_reports/conversation_metrics

# Expected response:
{
  "data": {
    "open": 42,
    "unattended": 5,
    "unassigned": 3,
    "pending": 0
  }
}

# Test grouped metrics by team
curl http://localhost:8000/api/v1/accounts/1/v2/live_reports/grouped_conversation_metrics?group_by=team_id

# Expected response:
{
  "data": [
    {
      "team_id": 1,
      "open": 10,
      "unattended": 2,
      "unassigned": 1
    },
    {
      "team_id": 2,
      "open": 15,
      "unattended": 3,
      "unassigned": 2
    }
  ]
}
```

## Related Files

- ✅ `laravel-svelte-port/laravel/app/Models/Conversation.php` - Added `scopeUnattended()`
- ✅ `laravel-svelte-port/laravel/app/Repositories/Reports/LiveReportsRepository.php` - Updated to use scope
- ✅ `laravel-svelte-port/laravel/database/migrations/2024_01_01_000028_create_conversations_table.php` - Schema reference

## Rails Parity

### ✅ Matching Rails Implementation

**Rails**:
```ruby
scope :unattended, -> { 
  where(first_reply_created_at: nil).or(where.not(waiting_since: nil)) 
}
```

**Laravel**:
```php
public function scopeUnattended($query)
{
    return $query->where(function ($q) {
        $q->whereNull('first_reply_created_at')
          ->orWhereNotNull('waiting_since');
    });
}
```

Both implementations:
- ✅ Check if `first_reply_created_at` is null
- ✅ Check if `waiting_since` is not null
- ✅ Use OR logic between conditions
- ✅ Return same results

## Status: ✅ FIXED

The `unattended` calculation now matches Rails implementation exactly. All live reports API endpoints should work without database errors.
