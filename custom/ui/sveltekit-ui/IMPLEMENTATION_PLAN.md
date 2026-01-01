# SvelteKit Super Admin Implementation Plan

**Project**: Chatwoot SvelteKit Super Admin SPA  
**Status**: Foundation Complete, Beginning Feature Implementation  
**Date**: 2026-01-01  
**Target**: Full UI/UX parity with Vue frontend

---

## Implementation Phases

### ✅ Phase 1: Foundation (COMPLETE)
- [x] SvelteKit project scaffolded
- [x] All 69 UI components copied from svelte-ui
- [x] API client with all endpoints
- [x] Auth store infrastructure
- [x] Onboarding flow complete
- [x] Documentation (README, ARCHITECTURE, STATUS)

---

### 🚧 Phase 2: Authentication System (IN PROGRESS)

#### 2.1 Login Page
**File**: `src/routes/login/+page.svelte`
**Features**:
- Email/password form with validation
- "Remember me" checkbox
- Error handling with toast notifications
- Loading states during login
- Redirect to dashboard on success
- Redirect to onboarding if no super admin exists
- Match Vue frontend styling exactly

**API Integration**:
- POST `/api/v1/login` with email & password
- Store token and user in authStore
- Handle validation errors

**UI Components Used**:
- Card, Input, Label, Button, Checkbox, Alert

#### 2.2 Route Guards
**File**: `src/routes/app/super_admin/+layout.ts`
**Features**:
- Check authentication status
- Redirect to /login if not authenticated
- Verify super admin role
- Initialize auth on app load

**File**: `src/routes/+layout.svelte`
**Features**:
- Initialize authStore on mount
- Set up global auth listeners

---

### 🎯 Phase 3: Dashboard (NEXT)

#### 3.1 Dashboard Layout
**File**: `src/routes/app/super_admin/dashboard/+page.svelte`

**Layout Structure** (matching Vue):
```
┌─────────────────────────────────────────┐
│         Super Admin Dashboard           │
├─────────────────────────────────────────┤
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐  │
│  │Accts │ │Users │ │Inbox │ │Convs │  │
│  │ 123  │ │ 456  │ │  78  │ │ 901  │  │
│  └──────┘ └──────┘ └──────┘ └──────┘  │
│                                         │
│  ┌───────────────────────────────────┐ │
│  │     Conversations Chart           │ │
│  │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐   │ │
│  │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘   │ │
│  └───────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

**Metric Cards**:
- Accounts count
- Users count
- Inboxes count
- Conversations count

**Chart Component**:
- Bar chart showing conversation trends
- Use Chart.js or similar library
- Match Vue chart styling

**API Endpoints**:
- GET `/api/v1/super_admin/dashboard`
- GET `/api/v1/super_admin/instance_status`

**UI Components**:
- Card for each metric
- Chart component (to be added)
- Skeleton loaders for loading states

---

### 📦 Phase 4: CRUD Module Template

**Pattern for All Modules**:

```
src/routes/app/super_admin/{module}/
├── +page.svelte          # List view with table
├── +page.ts              # Load data, check auth
├── [id]/
│   ├── +page.svelte      # Detail/edit view
│   └── +page.ts          # Load single item
└── new/
    └── +page.svelte      # Create new item
```

**Common Features**:
1. **List View**:
   - Data table with pagination
   - Search/filter functionality
   - Sort by columns
   - Actions (edit, delete)
   - "Create New" button

2. **Create/Edit Form**:
   - Form validation with Zod schemas
   - Error handling
   - Success/error notifications
   - Cancel button
   - Loading states

3. **Delete Confirmation**:
   - Dialog modal
   - Confirm action
   - API call with error handling

---

### 📋 Phase 5-14: Individual CRUD Modules

#### 5. Accounts Management
**Route**: `/app/super_admin/accounts`

**List View Features**:
- Table columns: ID, Name, Created At, Status, Actions
- Search by name
- Filter by status
- Pagination (20 per page)
- Actions: View, Edit, Delete, Seed Data, Reset Cache

**Form Fields**:
- Account name (required)
- Status (active/suspended)
- Locale
- Domain
- Auto-resolve duration
- Features enabled

**API Endpoints**:
- GET `/api/v1/super_admin/accounts`
- POST `/api/v1/super_admin/accounts`
- GET `/api/v1/super_admin/accounts/{id}`
- PUT `/api/v1/super_admin/accounts/{id}`
- DELETE `/api/v1/super_admin/accounts/{id}`
- POST `/api/v1/super_admin/accounts/{id}/seed`
- POST `/api/v1/super_admin/accounts/{id}/reset_cache`

---

#### 6. Users Management
**Route**: `/app/super_admin/users`

**List View Features**:
- Table columns: Avatar, Name, Email, Role, Created At, Actions
- Search by name or email
- Filter by role
- Pagination
- Actions: View, Edit, Delete Avatar, Delete User

**Form Fields**:
- Name (required)
- Email (required)
- Password (required for new)
- Role (super_admin/administrator/agent)
- Avatar upload
- Account assignments

**API Endpoints**:
- GET `/api/v1/super_admin/users`
- POST `/api/v1/super_admin/users`
- GET `/api/v1/super_admin/users/{id}`
- PUT `/api/v1/super_admin/users/{id}`
- DELETE `/api/v1/super_admin/users/{id}`
- DELETE `/api/v1/super_admin/users/{id}/avatar`

**Special Features**:
- Avatar preview and upload
- Role badge with colors
- Account membership display

---

#### 7. Settings Management
**Route**: `/app/super_admin/settings`

**Layout**: Tabbed interface (like Vue)

**Tabs**:
1. **General Settings**
   - Installation name
   - Logo upload
   - Brand color
   - Widget color

2. **Platform Settings**
   - Signup enabled
   - Account approval required
   - Email confirmation required

3. **System Settings**
   - Mailer settings
   - Storage settings
   - Cache settings

4. **Security Settings**
   - Force SSL
   - Session timeout
   - Password policy

5. **Integration Settings**
   - API keys
   - Webhook URLs
   - Third-party integrations

**Form Structure**:
- Grouped by category
- Toggle switches for boolean settings
- Input fields for text settings
- Select dropdowns for enum settings
- Color pickers for color settings

**API Endpoints**:
- GET `/api/v1/super_admin/settings`
- GET `/api/v1/super_admin/settings/show` (grouped)
- PATCH `/api/v1/super_admin/settings`
- POST `/api/v1/super_admin/settings`
- DELETE `/api/v1/super_admin/settings/{name}`
- GET `/api/v1/super_admin/settings/categories`
- POST `/api/v1/super_admin/settings/reset`

---

#### 8. Agent Bots Management
**Route**: `/app/super_admin/agent_bots`

**List View Features**:
- Table columns: Avatar, Name, Description, Type, Actions
- Search by name
- Filter by type
- Pagination
- Actions: View, Edit, Delete

**Form Fields**:
- Name (required)
- Description
- Bot type (CSM Bot, Outbound Bot)
- Avatar upload
- Configuration JSON
- Enabled/disabled toggle

**API Endpoints**:
- GET `/api/v1/super_admin/agent_bots`
- POST `/api/v1/super_admin/agent_bots`
- GET `/api/v1/super_admin/agent_bots/{id}`
- PUT `/api/v1/super_admin/agent_bots/{id}`
- DELETE `/api/v1/super_admin/agent_bots/{id}`
- DELETE `/api/v1/super_admin/agent_bots/{id}/avatar`

---

#### 9. Platform Apps Management
**Route**: `/app/super_admin/platform_apps`

**List View Features**:
- Table columns: Name, Description, Enabled, Actions
- Search by name
- Filter by enabled status
- Pagination
- Actions: View, Edit, Delete, Regenerate Token

**Form Fields**:
- Name (required)
- Description
- Enabled toggle
- Webhook URL
- Scopes/permissions

**API Endpoints**:
- GET `/api/v1/super_admin/platform_apps`
- POST `/api/v1/super_admin/platform_apps`
- GET `/api/v1/super_admin/platform_apps/{id}`
- PUT `/api/v1/super_admin/platform_apps/{id}`
- DELETE `/api/v1/super_admin/platform_apps/{id}`
- POST `/api/v1/super_admin/platform_apps/{id}/regenerate_token`

---

#### 10. Access Tokens Management
**Route**: `/app/super_admin/access_tokens`

**List View Features**:
- Table columns: Name, Token (partial), Created, Actions
- Search by name
- Pagination
- Actions: View, Revoke

**Create Form**:
- Name (required)
- Description
- Expiration date
- Show full token after creation (one-time display)

**API Endpoints**:
- GET `/api/v1/super_admin/access_tokens`
- POST `/api/v1/super_admin/access_tokens`
- GET `/api/v1/super_admin/access_tokens/{id}`
- DELETE `/api/v1/super_admin/access_tokens/{id}`
- DELETE `/api/v1/super_admin/users/{userId}/access_tokens`

**Special Features**:
- Copy token to clipboard
- Warning about one-time display
- Token revocation confirmation

---

#### 11. Installation Configs Management
**Route**: `/app/super_admin/installation_configs`

**Layout**: Similar to Settings, grouped by category

**List View Features**:
- Grouped display by category
- Search by key
- Filter by group
- Actions: Edit, Delete

**Form Fields**:
- Config key (required)
- Config value (required)
- Group/category
- Locked (boolean)
- Description

**API Endpoints**:
- GET `/api/v1/super_admin/installation_configs`
- POST `/api/v1/super_admin/installation_configs`
- GET `/api/v1/super_admin/installation_configs/groups`
- GET `/api/v1/super_admin/installation_configs/group/{group}`
- GET `/api/v1/super_admin/installation_configs/{id}`
- PATCH `/api/v1/super_admin/installation_configs/{id}`
- DELETE `/api/v1/super_admin/installation_configs/{id}`

---

#### 12. Account Users Management
**Route**: `/app/super_admin/account_users`

**List View Features**:
- Table columns: User Name, User Email, Account Name, Role, Actions
- Search by user or account
- Filter by role
- Pagination
- Actions: Edit Role, Remove

**Create Form**:
- Select User (dropdown with search)
- Select Account (dropdown with search)
- Select Role (agent/administrator)
- Availability status

**API Endpoints**:
- GET `/api/v1/super_admin/account_users`
- POST `/api/v1/super_admin/account_users`
- GET `/api/v1/super_admin/account_users/{id}`
- PUT `/api/v1/super_admin/account_users/{id}`
- DELETE `/api/v1/super_admin/account_users/{id}`
- POST `/api/v1/super_admin/account_users/bulk`
- GET `/api/v1/super_admin/account_users/stats`

---

#### 13. Audit Logs
**Route**: `/app/super_admin/audit_logs`

**List View Features** (Read-only):
- Table columns: Timestamp, User, Action, Model, Details, IP
- Search by user or action
- Filter by date range
- Filter by model type
- Filter by event type
- Pagination (50 per page)
- Export to CSV

**Detail View**:
- Full audit log details
- Old values vs new values comparison
- Related model information
- User information
- Timestamp and IP

**API Endpoints**:
- GET `/api/v1/super_admin/audit_logs`
- GET `/api/v1/super_admin/audit_logs/{id}`
- GET `/api/v1/super_admin/audit_logs/stats`
- POST `/api/v1/super_admin/audit_logs/export`
- POST `/api/v1/super_admin/audit_logs/cleanup`

**Special Features**:
- Date range picker
- Advanced filtering
- Export functionality
- Syntax highlighting for JSON diffs

---

#### 14. Cache Management
**Route**: `/app/super_admin/cache`

**Layout**: Dashboard-style with cards

**Cache Types**:
1. Application Cache
   - Show size
   - Clear button
   - Last cleared timestamp

2. Config Cache
   - Show size
   - Clear button

3. Route Cache
   - Show size
   - Clear button

4. View Cache
   - Show size
   - Clear button

5. Compiled Cache
   - Show size
   - Clear button

**Actions**:
- Clear All Cache (button)
- Clear by Type (individual buttons)
- Clear by Pattern (input + button)
- Clear Account Cache (select account + button)
- Warm Up Cache (button)

**API Endpoints**:
- GET `/api/v1/super_admin/cache`
- POST `/api/v1/super_admin/cache/clear`
- POST `/api/v1/super_admin/cache/clear/{type}`
- POST `/api/v1/super_admin/cache/clear_pattern`
- POST `/api/v1/super_admin/cache/clear_account/{id}`
- POST `/api/v1/super_admin/cache/warmup`

**Special Features**:
- Real-time cache size updates
- Confirmation dialogs for destructive actions
- Success/error notifications
- Loading indicators

---

### 🎨 Phase 15: Advanced UI Features

#### 15.1 Navigation Sidebar
**File**: `src/routes/app/super_admin/+layout.svelte`

**Structure**:
```
┌────────────────┬─────────────────────────┐
│ Logo           │   Page Content          │
│                │                         │
│ Dashboard      │                         │
│ Accounts       │                         │
│ Users          │                         │
│ Settings       │                         │
│ Agent Bots     │                         │
│ Platform Apps  │                         │
│ Access Tokens  │                         │
│ Configs        │                         │
│ Account Users  │                         │
│ Audit Logs     │                         │
│ Cache          │                         │
│                │                         │
│ [Logout]       │                         │
└────────────────┴─────────────────────────┘
```

**Features**:
- Active route highlighting
- Collapsible on mobile
- User info at bottom
- Logout button
- Icons for each section

**Components**:
- Use existing Sidebar component
- Custom navigation items
- Responsive behavior

#### 15.2 Search Functionality
**Component**: `src/lib/components/GlobalSearch.svelte`

**Features**:
- Command palette (Cmd+K / Ctrl+K)
- Search across all modules
- Quick navigation to pages
- Recent searches

**Integration**:
- Add to main layout
- Use Command component
- API search endpoints

#### 15.3 Notifications System
**Component**: `src/lib/components/Notifications.svelte`

**Features**:
- Bell icon with count
- Dropdown list
- Mark as read
- Clear all

**Types**:
- System notifications
- Admin actions
- Error alerts

#### 15.4 Data Tables
**Component**: `src/lib/components/DataTable.svelte`

**Features**:
- Sortable columns
- Pagination controls
- Row selection
- Bulk actions
- Loading states
- Empty states
- Error states

**Props**:
- columns (definition)
- data (rows)
- loading (boolean)
- pagination (object)
- onSort (function)
- onPageChange (function)
- onRowClick (function)

#### 15.5 Modal System
**Component**: Enhance existing Dialog component

**Types**:
- Confirmation modals
- Form modals
- Detail view modals
- Warning modals

**Features**:
- Keyboard navigation (Esc to close)
- Focus trap
- Backdrop click to close
- Animation

---

### 🧪 Phase 16: Testing

#### 16.1 Unit Tests
**Framework**: Vitest

**Test Files**:
- `src/lib/api/client.test.ts` - API client tests
- `src/lib/stores/auth.test.ts` - Auth store tests
- `src/lib/utils/index.test.ts` - Utility function tests

**Coverage Target**: 80%+

#### 16.2 Component Tests
**Framework**: Vitest + Testing Library

**Test Files**:
- Button, Input, Card components
- Form validation
- Error handling

#### 16.3 E2E Tests
**Framework**: Playwright

**Critical Flows**:
1. Onboarding flow
2. Login/Logout
3. Create account
4. Create user
5. Update settings
6. Delete operations

---

### 🚀 Phase 17: Production Optimization

#### 17.1 Performance
- [ ] Code splitting
- [ ] Lazy loading routes
- [ ] Image optimization
- [ ] Bundle analysis
- [ ] Tree shaking verification
- [ ] Preconnect to API domain

#### 17.2 Accessibility
- [ ] Screen reader testing
- [ ] Keyboard navigation testing
- [ ] ARIA labels audit
- [ ] Color contrast checks
- [ ] Focus indicators
- [ ] Alt text for images

#### 17.3 Security
- [ ] CSP headers
- [ ] XSS prevention audit
- [ ] CSRF protection verification
- [ ] Secure token storage
- [ ] Input sanitization
- [ ] API error handling

#### 17.4 SEO & Meta
- [ ] Page titles
- [ ] Meta descriptions
- [ ] Open Graph tags
- [ ] Favicon
- [ ] Robots.txt

---

## Implementation Order

### Week 1
- **Day 1**: Authentication pages (login, route guards)
- **Day 2**: Dashboard with metrics and charts
- **Day 3**: Accounts management (complete CRUD)
- **Day 4**: Users management (complete CRUD)
- **Day 5**: Settings management (tabbed interface)

### Week 2
- **Day 1**: Agent Bots management
- **Day 2**: Platform Apps management
- **Day 3**: Access Tokens management
- **Day 4**: Installation Configs management
- **Day 5**: Account Users management

### Week 3
- **Day 1**: Audit Logs (read-only with filtering)
- **Day 2**: Cache Management interface
- **Day 3**: Navigation sidebar and layout
- **Day 4**: Search, notifications, advanced features
- **Day 5**: Testing setup and critical flow tests

---

## Code Organization

### Directory Structure
```
src/
├── lib/
│   ├── api/
│   │   └── client.ts           # API client (existing)
│   ├── components/
│   │   ├── ui/                 # All 69 UI components (existing)
│   │   ├── layout/             # Layout components
│   │   │   ├── Sidebar.svelte
│   │   │   ├── Header.svelte
│   │   │   └── Footer.svelte
│   │   ├── features/           # Feature-specific components
│   │   │   ├── accounts/
│   │   │   ├── users/
│   │   │   └── ...
│   │   └── shared/             # Shared components
│   │       ├── DataTable.svelte
│   │       ├── SearchBar.svelte
│   │       ├── Pagination.svelte
│   │       └── ConfirmDialog.svelte
│   ├── stores/
│   │   ├── auth.ts             # Auth store (existing)
│   │   ├── ui.ts               # UI state
│   │   └── cache.ts            # Client-side cache
│   ├── utils/
│   │   ├── index.ts            # Utilities (existing)
│   │   ├── validation.ts       # Zod schemas
│   │   └── formatters.ts       # Date, number formatters
│   └── types/
│       ├── api.ts              # API response types
│       └── models.ts           # Domain models
├── routes/
│   ├── +layout.svelte          # Root layout with auth init
│   ├── +page.svelte            # Redirect to appropriate page
│   ├── onboarding/
│   │   └── +page.svelte        # (existing)
│   ├── login/
│   │   └── +page.svelte        # (to be created)
│   └── app/
│       └── super_admin/
│           ├── +layout.svelte  # Sidebar layout
│           ├── +layout.ts      # Auth guard
│           ├── dashboard/
│           ├── accounts/
│           ├── users/
│           ├── settings/
│           ├── agent-bots/
│           ├── platform-apps/
│           ├── access-tokens/
│           ├── installation-configs/
│           ├── account-users/
│           ├── audit-logs/
│           └── cache/
└── app.css                      # Global styles (existing)
```

---

## Validation Schemas

### Example: User Create/Update
```typescript
import { z } from 'zod';

export const userSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  email: z.string().email('Invalid email address'),
  password: z.string().min(8, 'Password must be at least 8 characters').optional(),
  role: z.enum(['super_admin', 'administrator', 'agent']),
  avatar: z.instanceof(File).optional()
});

export type UserFormData = z.infer<typeof userSchema>;
```

---

## API Response Handling

### Standard Pattern
```typescript
try {
  const data = await superAdminApi.getUsers({ page: 1, per_page: 20 });
  // Handle success
  toast.success('Users loaded successfully');
  return data;
} catch (error: any) {
  // Handle errors
  if (error.response?.status === 422) {
    // Validation errors
    return { errors: error.response.data.errors };
  } else if (error.response?.status === 401) {
    // Unauthorized (handled by interceptor, but can add extra logic)
    goto('/login');
  } else {
    // Generic error
    toast.error(error.message || 'An error occurred');
  }
}
```

---

## Styling Guidelines

### Match Vue Frontend Exactly
- Use same color palette from Vue app
- Match spacing and typography
- Use same icon set
- Copy exact button styles
- Match form input styles
- Use same table styling
- Replicate dashboard card design

### Tailwind Classes
- Consistent spacing: `p-4`, `p-6`, `p-8`
- Consistent gaps: `gap-4`, `gap-6`
- Consistent rounded corners: `rounded-lg`
- Consistent shadows: `shadow-sm`, `shadow-md`

---

## Next Steps

1. **Reply to user confirming plan**
2. **Implement Authentication pages** (login + guards)
3. **Implement Dashboard** with metrics
4. **Start CRUD modules** one by one
5. **Commit incrementally** after each complete feature
6. **Update this plan** as context for next iteration

---

## Progress Tracking

Use this checklist to track implementation:

- [ ] Phase 2: Authentication System
  - [ ] Login page
  - [ ] Route guards
  - [ ] Logout functionality
- [ ] Phase 3: Dashboard
  - [ ] Metrics cards
  - [ ] Chart component
  - [ ] Instance status
- [ ] Phase 4-14: CRUD Modules
  - [ ] Accounts
  - [ ] Users
  - [ ] Settings
  - [ ] Agent Bots
  - [ ] Platform Apps
  - [ ] Access Tokens
  - [ ] Installation Configs
  - [ ] Account Users
  - [ ] Audit Logs
  - [ ] Cache Management
- [ ] Phase 15: Advanced Features
  - [ ] Navigation sidebar
  - [ ] Search
  - [ ] Notifications
  - [ ] Data tables
  - [ ] Modals
- [ ] Phase 16: Testing
- [ ] Phase 17: Production Optimization

---

**This plan serves as the complete implementation guide and context for all future iterations.**
