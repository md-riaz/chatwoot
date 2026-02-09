# Common Pitfalls to Avoid

## General Migration Pitfalls

### 1. ❌ Don't mix Rails and Laravel patterns
Use Laravel conventions consistently throughout the codebase.

### 2. ❌ Avoid heavy controllers
Keep business logic in Actions, not controllers.

**Bad:**
```php
public function store(Request $request) {
    $account = Account::create($request->all());
    Mail::to($account->email)->send(new WelcomeEmail());
    event(new AccountCreated($account));
    return response()->json($account);
}
```

**Good:**
```php
public function store(Request $request, CreateAccountAction $action) {
    $account = $action->execute(CreateAccountData::from($request->validated()));
    return response()->json($account);
}
```

### 3. ❌ Don't ignore type safety
Always use DTOs and TypeScript.

### 4. ❌ Avoid direct DB queries in controllers
Use Repositories for data access.

### 5. ❌ Don't forget error handling
Implement proper exception handling and logging.

### 6. ❌ Avoid inline styles
Use Tailwind CSS classes consistently.

## API Transformation Pitfalls

### 7. ❌ NEVER manually convert camelCase/snake_case
The API transformation layer handles this automatically.

**Bad:**
```typescript
const data = {
  users_count: usersCount, // Manual conversion
  inboxes_count: inboxesCount
};
```

**Good:**
```typescript
const data = {
  usersCount, // Automatic conversion
  inboxesCount
};
```

### 8. ❌ Always use camelCase in frontend code
Even when you know the backend uses snake_case.

### 9. ❌ Don't bypass the API client
Always use the configured API client to ensure transformations work.

## Feature Migration Pitfalls

### 10. ❌ Don't migrate excluded features
Skip copilot/captain functionality entirely.

### 11. ❌ Don't leave AI feature stubs
Remove AI-related code completely rather than leaving empty implementations.

### 12. ❌ Don't enable AI feature flags
Ensure copilot/captain features remain disabled in all environments.

## Laravel-Native Implementation Pitfalls

### 13. ❌ Don't create custom implementations for existing packages
Use Laravel ecosystem packages (Spatie, etc.).

**Bad:**
```php
trait CustomAvatarable {
    // 400+ lines of custom logic
}
```

**Good:**
```php
class User extends Model implements HasMedia {
    use HasAvatar; // Laravel-native trait
}
```

### 14. ❌ Don't reinvent Laravel's built-in features

**Bad:**
```php
class ComplexAvatarFromGravatarJob implements ShouldQueue {
    // Complex custom job class
}
```

**Good:**
```php
dispatch(function () use ($model) {
    $model->fetchGravatarAvatar();
})->delay(now()->addSeconds(30));
```

### 15. ❌ Don't copy Rails internal patterns
Focus on functional parity, not implementation parity.

- ✅ Same API endpoints and responses
- ✅ Same user-facing functionality
- ❌ Don't copy Rails internal patterns
- ❌ Don't add Rails-specific fields unless needed

## Laravel Configuration Pitfalls

### 16. ❌ NEVER use `app()` helper in config files
Config files are loaded before the application container.

**Bad:**
```php
'enable_feature' => env('ENABLE_FEATURE', !app()->environment('production'))
```

**Good:**
```php
'enable_feature' => env('ENABLE_FEATURE', env('APP_ENV', 'production') !== 'production')
```

### 17. ❌ Don't use Laravel helpers in config loading
Stick to basic PHP and `env()` function only.

### 18. ❌ Always provide fallback values in config
Use `env('KEY', 'default_value')` pattern consistently.

## Laravel-Rails API Parity Pitfalls

### 19. ❌ NEVER override Laravel pagination format
Use `transform()` on collections, maintain Laravel's standard structure.

### 20. ❌ Always check Rails backend first
Examine Rails controllers/serializers before implementing Laravel endpoints.

### 21. ❌ Don't use Laravel-specific field names in API responses
Maintain Rails field naming for compatibility.

**Bad:**
```php
'email_verified' => !is_null($user->email_verified_at)
```

**Good:**
```php
'confirmed' => !is_null($user->email_verified_at)
```

### 22. ❌ Don't ignore Rails relationship structures
Include all Rails relationship data in Laravel API responses.

### 23. ❌ Don't skip enum transformations
Convert Laravel enums to Rails-compatible string values.

**Bad:**
```php
'role' => $user->role // Returns enum object
```

**Good:**
```php
'role' => $user->getRoleNames()->first() ?? 'agent'
```

### 24. ❌ Don't use different timestamp formats
Always use `toISOString()` for Rails compatibility.

**Bad:**
```php
'created_at' => $user->created_at->format('Y-m-d H:i:s')
```

**Good:**
```php
'created_at' => $user->created_at?->toISOString()
```

### 25. ❌ Don't forget to update TypeScript interfaces
Match Laravel pagination response structure in frontend types.

### 26. ❌ Don't bypass Laravel conventions for Rails compatibility
Transform data while preserving Laravel patterns.

### 27. ❌ Don't use incorrect pagination field names
Use `last_page` not `total_pages`, `total` not `count`.

**Bad:**
```typescript
totalPages = response.meta?.total_pages || 1;
```

**Good:**
```typescript
totalPages = response.last_page || 1;
```

### 28. ❌ Don't mix pagination formats
Be consistent within the same application area (prefer Laravel standard).

### 29. ❌ Don't ignore Laravel's built-in pagination
Use `paginate()` method instead of custom pagination logic.

## Frontend Pitfalls

### 30. ❌ Don't use Vue patterns in Svelte
Follow Svelte 5 runes patterns.

**Bad (Vue-style):**
```svelte
<script>
  import { ref, computed } from 'vue';
  const count = ref(0);
  const doubled = computed(() => count.value * 2);
</script>
```

**Good (Svelte 5):**
```svelte
<script>
  let count = $state(0);
  let doubled = $derived(count * 2);
</script>
```

### 31. ❌ Don't forget reactivity
Use runes for reactive state.

### 32. ❌ Don't ignore TypeScript errors
Fix type issues immediately.

### 33. ❌ Don't skip component testing
Write tests for all components.

## Testing Pitfalls

### 34. ❌ Don't skip tests
Write tests for all new features.

### 35. ❌ Don't test implementation details
Test behavior, not internals.

### 36. ❌ Don't forget edge cases
Test error conditions and boundary cases.

## Performance Pitfalls

### 37. ❌ Don't load unnecessary data
Use eager loading and select only needed fields.

**Bad:**
```php
$users = User::all(); // Loads everything
```

**Good:**
```php
$users = User::select('id', 'name', 'email')
    ->with('roles:id,name')
    ->paginate(25);
```

### 38. ❌ Don't forget to cache
Use Laravel's caching for expensive operations.

### 39. ❌ Don't ignore N+1 queries
Use eager loading to prevent N+1 problems.

## Security Pitfalls

### 40. ❌ Don't skip validation
Always validate user input.

### 41. ❌ Don't expose sensitive data
Use API resources to control response data.

### 42. ❌ Don't forget authorization
Check permissions before allowing actions.

### 43. ❌ Don't store sensitive data in logs
Sanitize log output.

## Quick Checklist

Before committing code, verify:

- [ ] No manual case conversion (camelCase/snake_case)
- [ ] Using Laravel standard pagination
- [ ] Rails field names maintained in API responses
- [ ] TypeScript interfaces updated
- [ ] Tests written and passing
- [ ] No AI features included
- [ ] Error handling implemented
- [ ] Authorization checks in place
- [ ] No N+1 queries
- [ ] Proper logging without sensitive data
