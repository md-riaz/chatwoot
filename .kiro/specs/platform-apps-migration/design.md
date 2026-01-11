# Design Document

## Overview

This design outlines the implementation of a comprehensive Platform Apps management interface in SvelteKit that replaces the basic Rails Administrate interface. The design focuses on creating a modern, user-friendly interface that provides enhanced functionality while maintaining compatibility with the existing Laravel API backend.

The implementation will consist of:
1. **Enhanced SvelteKit Frontend**: Modern UI components with better UX than Rails Administrate
2. **API Client Improvements**: Complete TypeScript integration with proper error handling
3. **Laravel API Enhancements**: Minor improvements for better validation and error responses

## Architecture

### Frontend Architecture (SvelteKit)

```
src/routes/app/super_admin/platform-apps/
├── +page.svelte                    # List page (existing, needs fixes)
├── new/
│   └── +page.svelte               # Creation page (new)
├── [id]/
│   ├── +page.svelte               # Detail page (existing, needs enhancements)
│   └── edit/
│       └── +page.svelte           # Edit page (new)
```

### API Client Structure

```
src/lib/api/superAdmin.ts
├── PlatformApp interface          # TypeScript definitions
├── platformApps methods           # CRUD operations
└── Error handling                 # Consistent error management
```

### Laravel API Structure (Existing - No Changes Needed)

```
laravel/app/Http/Controllers/Api/V1/SuperAdmin/PlatformAppsController.php
├── index()                        # List with search/pagination ✓
├── store()                        # Create new platform app ✓
├── show()                         # Get platform app details ✓
├── update()                       # Update platform app ✓
└── destroy()                      # Delete platform app ✓
```

*Note: The regenerateToken() method exists but won't be used in the frontend since Rails parity doesn't include token regeneration UI.*

## Components and Interfaces

### 1. Platform App List Page (Enhanced)

**Current Issues to Fix:**
- Remove non-existent `webhook_url` column
- Add proper token masking with reveal functionality
- Fix pagination to use Laravel standard format
- Improve loading and error states

**Component Structure:**
```svelte
<script lang="ts">
  // State management
  let platformApps: PlatformApp[] = [];
  let loading = true;
  let searchQuery = '';
  let currentPage = 1;
  let totalPages = 1;

  // Enhanced columns configuration
  const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { 
      key: 'access_token', 
      label: 'Access Token', 
      sortable: false,
      render: (value, row) => TokenDisplay({ token: value, masked: true })
    },
    { 
      key: 'created_at', 
      label: 'Created At', 
      sortable: true,
      render: (value) => formatDate(value)
    }
  ];
</script>
```

### 2. Platform App Creation Page (New)

**Route:** `/app/super_admin/platform-apps/new`

**Component Structure:**
```svelte
<script lang="ts">
  import { goto } from '$app/navigation';
  import { api } from '$lib/api/superAdmin';
  import { toast } from 'svelte-sonner';

  let formData = {
    name: ''
  };
  let saving = false;
  let errors: Record<string, string> = {};

  async function handleSubmit() {
    try {
      saving = true;
      errors = {};
      
      const platformApp = await api.platformApps.create(formData);
      toast.success('Platform app created successfully');
      goto(`/app/super_admin/platform-apps/${platformApp.id}`);
    } catch (error: any) {
      if (error.status === 422) {
        errors = error.errors || {};
      }
      toast.error(error.message || 'Failed to create platform app');
    } finally {
      saving = false;
    }
  }
</script>

<form on:submit|preventDefault={handleSubmit}>
  <div class="space-y-6">
    <div class="space-y-2">
      <Label for="name">Name *</Label>
      <Input
        id="name"
        bind:value={formData.name}
        placeholder="Enter platform app name"
        required
        class:border-red-500={errors.name}
      />
      {#if errors.name}
        <p class="text-sm text-red-600">{errors.name}</p>
      {/if}
    </div>
    
    <div class="flex gap-3">
      <Button type="submit" disabled={saving}>
        {saving ? 'Creating...' : 'Create Platform App'}
      </Button>
      <Button variant="outline" href="/app/super_admin/platform-apps">
        Cancel
      </Button>
    </div>
  </div>
</form>
```

### 3. Platform App Detail Page (View Only)

**Route:** `/app/super_admin/platform-apps/[id]`

**Purpose:** Display Platform App information in read-only format with token visibility controls

**Component Structure:**
```svelte
<script lang="ts">
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { api } from '$lib/api/superAdmin';
  import { Eye, EyeOff, Copy, Edit, Trash2 } from 'lucide-svelte';
  
  const id = $page.params.id;
  let platformApp: PlatformApp | null = null;
  let loading = true;
  let showToken = false;

  async function loadPlatformApp() {
    try {
      loading = true;
      platformApp = await api.platformApps.get(id);
    } catch (error: any) {
      toast.error(error.message || 'Failed to load platform app');
    } finally {
      loading = false;
    }
  }

  function toggleTokenVisibility() {
    showToken = !showToken;
  }

  async function copyToken() {
    if (platformApp?.access_token) {
      try {
        await navigator.clipboard.writeText(platformApp.access_token);
        toast.success('Token copied to clipboard');
      } catch (error) {
        toast.error('Failed to copy token');
      }
    }
  }

  function maskToken(token: string): string {
    if (!token || token.length < 8) return '••••••••';
    return '••••••••' + token.slice(-8);
  }
</script>

<!-- Header with Actions -->
<div class="border-b border-slate-6 px-8 py-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-slate-12">Platform App Details</h1>
      <p class="mt-1 text-sm text-slate-11">
        <a href="/app/super_admin/platform-apps" class="hover:text-iris-9">Platform Apps</a>
        / {platformApp?.name || 'Loading...'}
      </p>
    </div>
    <div class="flex gap-3">
      <Button variant="outline" on:click={() => goto(`/app/super_admin/platform-apps/${id}/edit`)}>
        <Edit class="h-4 w-4 mr-2" />
        Edit
      </Button>
      <Button variant="destructive" on:click={() => (showDeleteDialog = true)}>
        <Trash2 class="h-4 w-4 mr-2" />
        Delete
      </Button>
    </div>
  </div>
</div>

<!-- Content -->
<div class="p-8">
  {#if loading}
    <div class="space-y-6">
      <Skeleton className="h-20 w-full" />
      <Skeleton className="h-20 w-full" />
    </div>
  {:else if platformApp}
    <div class="mx-auto max-w-3xl space-y-8">
      <!-- Basic Information (Read-only) -->
      <div class="space-y-6">
        <div class="space-y-2">
          <Label>Name</Label>
          <div class="px-3 py-2 bg-slate-2 rounded-md text-slate-12">
            {platformApp.name}
          </div>
        </div>

        <!-- Token Display with Visibility Toggle -->
        <div class="space-y-2">
          <Label>API Token</Label>
          <div class="flex gap-2">
            <Input
              value={showToken ? platformApp.access_token : maskToken(platformApp.access_token)}
              readonly
              class="flex-1 font-mono"
            />
            <Button
              variant="outline"
              size="icon"
              on:click={toggleTokenVisibility}
              title={showToken ? 'Hide token' : 'Show token'}
            >
              {#if showToken}
                <EyeOff class="h-4 w-4" />
              {:else}
                <Eye class="h-4 w-4" />
              {/if}
            </Button>
            {#if showToken}
              <Button
                variant="outline"
                size="icon"
                on:click={copyToken}
                title="Copy token"
              >
                <Copy class="h-4 w-4" />
              </Button>
            {/if}
          </div>
          <p class="text-sm text-slate-11">
            {showToken ? 'Token is visible. Click the eye icon to hide it.' : 'Token is hidden for security. Click the eye icon to reveal it.'}
          </p>
        </div>

        <!-- Metadata (Read-only) -->
        <div class="grid grid-cols-2 gap-6">
          <div class="space-y-2">
            <Label>Created At</Label>
            <div class="px-3 py-2 bg-slate-2 rounded-md text-slate-12">
              {new Date(platformApp.created_at).toLocaleString()}
            </div>
          </div>
          <div class="space-y-2">
            <Label>Updated At</Label>
            <div class="px-3 py-2 bg-slate-2 rounded-md text-slate-12">
              {new Date(platformApp.updated_at).toLocaleString()}
            </div>
          </div>
        </div>
      </div>
    </div>
  {/if}
</div>
```

### 4. Reusable Token Display Component

**Component:** `src/lib/components/TokenDisplay.svelte`

```svelte
<script lang="ts">
  import { Eye, EyeOff, Copy } from 'lucide-svelte';
  import { toast } from 'svelte-sonner';
  import Button from '$lib/components/ui/button/button.svelte';

  let { token, masked = true, size = 'default' } = $props();
  let showToken = $state(!masked);

  function toggleVisibility() {
    showToken = !showToken;
  }

  async function copyToken() {
    try {
      await navigator.clipboard.writeText(token);
      toast.success('Token copied to clipboard');
    } catch (error) {
      toast.error('Failed to copy token');
    }
  }

  function maskToken(token: string): string {
    if (!token || token.length < 8) return '••••••••';
    return '••••••••' + token.slice(-8);
  }
</script>

<div class="flex items-center gap-2">
  <code class="text-sm font-mono bg-slate-2 px-2 py-1 rounded">
    {showToken ? token : maskToken(token)}
  </code>
  
  <Button
    variant="ghost"
    size="sm"
    on:click={toggleVisibility}
    title={showToken ? 'Hide token' : 'Show token'}
  >
    {#if showToken}
      <EyeOff class="h-3 w-3" />
    {:else}
      <Eye class="h-3 w-3" />
    {/if}
  </Button>
  
  {#if showToken}
    <Button
      variant="ghost"
      size="sm"
      on:click={copyToken}
      title="Copy token"
    >
      <Copy class="h-3 w-3" />
    </Button>
  {/if}
</div>
```

## Data Models

### TypeScript Interfaces

```typescript
// Enhanced PlatformApp interface
export interface PlatformApp {
  id: number;
  name: string;
  access_token: string;
  created_at: string;
  updated_at: string;
  // Optional fields for future expansion
  permissibles?: PlatformAppPermissible[];
}

export interface PlatformAppPermissible {
  id: number;
  platform_app_id: number;
  permissible_type: string;
  permissible_id: number;
  created_at: string;
  updated_at: string;
}

// API Response types
export interface PlatformAppsListResponse extends LaravelPaginationResponse<PlatformApp> {}

export interface PlatformAppResponse {
  data: PlatformApp;
}

// Form data types
export interface CreatePlatformAppData {
  name: string;
}

export interface UpdatePlatformAppData {
  name?: string;
}
```

### API Client Methods

```typescript
// Enhanced API client methods
export const platformAppsApi = {
  // List platform apps with search and pagination
  list: async (params?: PaginationParams): Promise<PlatformAppsListResponse> => {
    return api.get('api/v1/super_admin/platform_apps', { 
      searchParams: params as Record<string, string> 
    }).json();
  },

  // Get single platform app
  get: async (id: number): Promise<PlatformApp> => {
    const response = await api.get(`api/v1/super_admin/platform_apps/${id}`).json<PlatformAppResponse>();
    return response.data;
  },

  // Create new platform app
  create: async (data: CreatePlatformAppData): Promise<PlatformApp> => {
    const response = await api.post('api/v1/super_admin/platform_apps', { 
      json: data 
    }).json<PlatformAppResponse>();
    return response.data;
  },

  // Update platform app
  update: async (id: number, data: UpdatePlatformAppData): Promise<PlatformApp> => {
    const response = await api.put(`api/v1/super_admin/platform_apps/${id}`, { 
      json: data 
    }).json<PlatformAppResponse>();
    return response.data;
  },

  // Delete platform app
  delete: async (id: number): Promise<void> => {
    await api.delete(`api/v1/super_admin/platform_apps/${id}`);
  }
};
```

## Laravel API Enhancements

### 1. Improved Validation and Error Handling

```php
// Enhanced validation in PlatformAppsController
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:platform_apps,name',
    ], [
        'name.required' => 'Platform app name is required.',
        'name.unique' => 'A platform app with this name already exists.',
        'name.max' => 'Platform app name cannot exceed 255 characters.',
    ]);

    $app = PlatformApp::create($validated);

    return response()->json([
        'data' => [
            'id' => $app->id,
            'name' => $app->name,
            'access_token' => $app->access_token,
            'created_at' => $app->created_at->toISOString(),
            'updated_at' => $app->updated_at->toISOString(),
        ],
    ], 201);
}

public function update(Request $request, PlatformApp $platformApp): JsonResponse
{
    $validated = $request->validate([
        'name' => 'sometimes|string|max:255|unique:platform_apps,name,' . $platformApp->id,
    ], [
        'name.unique' => 'A platform app with this name already exists.',
        'name.max' => 'Platform app name cannot exceed 255 characters.',
    ]);

    $platformApp->update($validated);

    return response()->json([
        'data' => [
            'id' => $platformApp->id,
            'name' => $platformApp->name,
            'access_token' => $platformApp->access_token,
            'created_at' => $platformApp->created_at->toISOString(),
            'updated_at' => $platformApp->updated_at->toISOString(),
        ],
    ]);
}
```

### 2. Consistent Response Format

```php
// Ensure all endpoints return consistent timestamp format
private function formatPlatformAppResponse(PlatformApp $app): array
{
    return [
        'id' => $app->id,
        'name' => $app->name,
        'access_token' => $app->access_token,
        'created_at' => $app->created_at->toISOString(),
        'updated_at' => $app->updated_at->toISOString(),
    ];
}
```

## Error Handling

### Frontend Error Handling

```typescript
// Centralized error handling for Platform Apps
export class PlatformAppError extends Error {
  constructor(
    message: string,
    public status?: number,
    public errors?: Record<string, string>
  ) {
    super(message);
    this.name = 'PlatformAppError';
  }
}

// Error handling in API client
async function handleApiError(response: Response): Promise<never> {
  const contentType = response.headers.get('content-type');
  
  if (contentType?.includes('application/json')) {
    const errorData = await response.json();
    
    if (response.status === 422) {
      // Validation errors
      throw new PlatformAppError(
        errorData.message || 'Validation failed',
        422,
        errorData.errors
      );
    }
    
    throw new PlatformAppError(
      errorData.message || 'An error occurred',
      response.status
    );
  }
  
  throw new PlatformAppError(
    `HTTP ${response.status}: ${response.statusText}`,
    response.status
  );
}
```

### Backend Error Handling

```php
// Enhanced error handling in Laravel controller
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

public function show(PlatformApp $platformApp): JsonResponse
{
    try {
        return response()->json([
            'data' => $this->formatPlatformAppResponse($platformApp),
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Platform app not found.',
        ], 404);
    }
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Based on the prework analysis, the following properties ensure the Platform Apps functionality works correctly across all scenarios:

### Property 1: API Error Handling Consistency
*For any* Platform App API operation that encounters an error, the system should handle the error consistently with proper error types, status codes, and user feedback.
**Validates: Requirements 3.1, 6.2**

### Property 2: API Response Format Consistency  
*For any* Platform App API endpoint response, the response format should be consistent with proper data structure, timestamps in ISO format, and Laravel pagination format where applicable.
**Validates: Requirements 3.4, 3.5, 6.1**

### Property 3: Loading State Display
*For any* Platform App operation that involves API calls, the system should display loading indicators during the operation and hide them when complete.
**Validates: Requirements 8.1**

### Property 4: Operation Feedback Notifications
*For any* Platform App operation (create, update, delete), the system should display appropriate success or error toast notifications to provide user feedback.
**Validates: Requirements 8.2**

### Property 5: Form State Preservation During Errors
*For any* Platform App form that encounters validation errors, the system should preserve the user's input data and display specific error messages without losing form state.
**Validates: Requirements 8.3**

### Property 6: Comprehensive Error Handling
*For any* error scenario in Platform App operations, the system should handle the error gracefully with appropriate user feedback and maintain system stability.
**Validates: Requirements 10.5**

## Testing Strategy

### Property-Based Testing

Each correctness property will be implemented as property-based tests using Vitest and fast-check for the frontend, and Pest with custom generators for the backend:

**Frontend Property Tests:**
```typescript
// Property 1: API Error Handling Consistency
test('API operations handle errors consistently', async () => {
  await fc.assert(fc.asyncProperty(
    fc.constantFrom('create', 'update', 'delete', 'get', 'list'),
    fc.record({ name: fc.string() }),
    async (operation, data) => {
      // Simulate API error
      const mockError = new Error('API Error');
      mockError.status = 500;
      
      // Verify consistent error handling
      const result = await handlePlatformAppOperation(operation, data);
      expect(result.error).toBeDefined();
      expect(result.userFeedback).toBeDefined();
    }
  ));
});

// Property 3: Loading State Display  
test('loading states appear for all operations', async () => {
  await fc.assert(fc.asyncProperty(
    fc.constantFrom('create', 'update', 'delete'),
    async (operation) => {
      const component = render(PlatformAppComponent);
      
      // Trigger operation
      await triggerOperation(component, operation);
      
      // Verify loading state appears
      expect(component.getByTestId('loading-indicator')).toBeInTheDocument();
    }
  ));
});
```

**Backend Property Tests:**
```php
// Property 2: API Response Format Consistency
test('all platform app endpoints return consistent format', function () {
    $endpoints = ['index', 'show', 'store', 'update'];
    
    foreach ($endpoints as $endpoint) {
        $response = callPlatformAppEndpoint($endpoint);
        
        // Verify consistent response structure
        expect($response)->toHaveStructure([
            'data' => ['id', 'name', 'access_token', 'created_at', 'updated_at']
        ]);
        
        // Verify ISO timestamp format
        expect($response['data']['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3}Z$/');
    }
});
```

### Unit Testing

**Frontend Unit Tests:**
- Component rendering with different props and states
- Token masking and visibility toggle functionality
- Form validation and submission handling
- Navigation and routing behavior
- Copy-to-clipboard functionality

**Backend Unit Tests:**
- Model relationships and token generation
- Validation rules and error messages
- Controller method responses
- Database operations and constraints

### Integration Testing

**End-to-End Workflows:**
- Complete Platform App creation workflow
- Platform App listing with search and pagination
- Platform App detail viewing and editing
- Platform App deletion with confirmation
- Error handling across all operations

**Example Integration Test:**
```typescript
test('complete platform app management workflow', async () => {
  // Navigate to platform apps list
  await page.goto('/app/super_admin/platform-apps');
  
  // Create new platform app
  await page.click('[data-testid="new-platform-app"]');
  await page.fill('[data-testid="name-input"]', 'Test App');
  await page.click('[data-testid="submit-button"]');
  
  // Verify creation success and navigation
  await expect(page).toHaveURL(/\/platform-apps\/\d+/);
  await expect(page.locator('[data-testid="success-toast"]')).toBeVisible();
  
  // Verify token display and copy functionality
  await page.click('[data-testid="show-token-button"]');
  await expect(page.locator('[data-testid="access-token"]')).toBeVisible();
  
  await page.click('[data-testid="copy-token-button"]');
  await expect(page.locator('[data-testid="copy-success-toast"]')).toBeVisible();
  
  // Update platform app
  await page.fill('[data-testid="name-input"]', 'Updated Test App');
  await page.click('[data-testid="save-button"]');
  await expect(page.locator('[data-testid="update-success-toast"]')).toBeVisible();
  
  // Delete platform app
  await page.click('[data-testid="delete-button"]');
  await page.click('[data-testid="confirm-delete"]');
  await expect(page).toHaveURL('/app/super_admin/platform-apps');
  await expect(page.locator('[data-testid="delete-success-toast"]')).toBeVisible();
});
```

### Testing Configuration

**Property Test Settings:**
- Minimum 100 iterations per property test
- Each test tagged with: **Feature: platform-apps-migration, Property {number}: {property_text}**
- Custom generators for Platform App data structures
- Error simulation for comprehensive error handling testing

**Test Data Management:**
- Factory patterns for consistent test data generation
- Database seeding for integration tests
- Mock API responses for frontend unit tests
- Cleanup procedures for test isolation