# Svelte UI Implementation Plan

## Current Status

### ✅ Completed: 66 Primitive Components
All basic UI primitives are implemented with Histoire stories:
- Form controls (Button, Input, Checkbox, Switch, etc.)
- Data display (Badge, Avatar, Card, Spinner, etc.)
- Navigation (Tabs, Breadcrumb, Pagination, etc.)
- Overlays (Dialog, Popover, Tooltip, Sheet, etc.)
- Layout (Separator, Sidebar, Scroll Area, etc.)

### 🎯 Next Phase: Application-Specific Components

Based on the Vue implementation, we need to add application features organized by module:

## Implementation Roadmap

### Phase 1: Core Features (High Priority) 🔴

#### 1. File Handling Components
**Priority:** HIGH - Essential for messaging
- **File Upload** - Drag-and-drop file upload with preview
- **Audio Recorder** - Voice message recording interface
- **Emoji Picker** - Emoji selection for messages
- **Image Gallery** - Image viewing and selection

**Estimated:** 2-3 days

#### 2. Calendar & Date Components  
**Priority:** HIGH - Required for scheduling and date selection
- **Calendar** - Date picker calendar component
- **Date Picker** - Date selection with input
- **Date Range Picker** - Select date ranges

**Estimated:** 2-3 days

#### 3. Form Validation
**Priority:** HIGH - Essential for all forms
- **Form** - Formsnap integration with validation
- **Field** - Form field wrapper
- **Error Display** - Validation error messages

**Estimated:** 1-2 days

### Phase 2: Help Center Module (Medium Priority) 🟡

#### Components Needed:
1. **ArticlesPage** - Article management dashboard
2. **CategoriesPage** - Category management dashboard
3. **LocalesPage** - Locale management dashboard
4. **PortalSettings** - Portal configuration interface
5. **Article Empty State** - No articles placeholder
6. **Portal Empty State** - No portal placeholder
7. **Article Editor** - Rich text article editor
8. **Article Properties** - Article metadata editor

**Estimated:** 5-7 days

**Dependencies:**
- Rich text editor (TipTap or similar)
- Markdown support
- Code syntax highlighting

### Phase 3: Contact Management Module (Medium Priority) 🟡

#### Components Needed:
1. **Contact Merge Form** - Merge duplicate contacts
2. **Contact Import Dialog** - CSV import interface
3. **Contact Export Dialog** - Export contacts to CSV
4. **Create/Edit Contact Dialog** - Full CRUD operations
5. **Contact Labels** - Label management UI
6. **Contact Segments** - Segmentation interface
7. **Contact List** - Full contact list view
8. **Contact Details Layout** - Contact detail page

**Estimated:** 5-7 days

**Dependencies:**
- CSV parsing library
- Label management system
- Custom field support

### Phase 4: Conversation Module (High Priority) 🔴

#### Components Needed:
1. **Conversation List** - Main inbox view
2. **Conversation Details** - Full conversation display
3. **Message Thread** - Message history
4. **SLA Card Label** - SLA status indicators
5. **Priority Icon** - Priority indicators
6. **Conversation Filters** - Filter conversations
7. **Message Preview** - Conversation preview card

**Estimated:** 7-10 days

**Dependencies:**
- Real-time updates (WebSocket)
- Message parsing and rendering
- File attachments handling

### Phase 5: Captain AI Module (Low Priority) 🟢

#### Components Needed:
1. **Add New Rules Dialog** - AI rule creation
2. **Document Upload** - AI document management
3. **Response Suggestions** - AI-generated responses
4. **Rule Management** - AI rule configuration
5. **Scenarios Card** - AI scenario setup
6. **Tools Dropdown** - AI tool selection
7. **Settings Panel** - AI configuration

**Estimated:** 5-7 days

**Dependencies:**
- AI API integration
- Document processing
- Rule engine interface

### Phase 6: Assignment Policy Module (Low Priority) 🟢

#### Components Needed:
1. **Agent Capacity Policy** - Capacity management
2. **Assignment Policy Card** - Policy configuration
3. **Data Table** - Policy data display
4. **Exclusion Rules** - Rule exclusions
5. **Fair Distribution** - Load balancing
6. **Inbox Capacity Limits** - Capacity settings

**Estimated:** 4-5 days

**Dependencies:**
- Policy engine integration
- Agent availability system
- Capacity calculation logic

### Phase 7: Filter Module (Medium Priority) 🟡

#### Components Needed:
1. **Active Filter Preview** - Display active filters
2. **Condition Row** - Filter condition builder
3. **Filter Select** - Filter dropdown
4. **Multi Select Filter** - Multiple selection
5. **Single Select Filter** - Single selection
6. **Filter Save/Load** - Save filter presets

**Estimated:** 3-4 days

**Dependencies:**
- Filter query builder
- Filter persistence
- Advanced filter operators

### Phase 8: Additional Features (Low Priority) 🟢

#### Components Needed:
1. **Notification Center** - In-app notifications
2. **Search Component** - Global search
3. **User Menu** - User profile dropdown
4. **Settings Panel** - App settings
5. **Report Dashboard** - Analytics
6. **Team Management** - Team UI

**Estimated:** 5-7 days

## Implementation Guidelines

### 1. Component Structure
Each application component should follow this pattern:

```
component-name/
├── index.ts                    # Exports and types
├── component-name.svelte       # Main component
├── ComponentName.story.svelte  # Histoire story
├── sub-component.svelte        # Sub-components if needed
└── types.ts                    # TypeScript types
```

### 2. Story Requirements
Each component must have a Histoire story with:
- Default state
- All variants/states
- Interactive examples
- Real-world usage examples
- Props documentation

### 3. API Integration
Use the established pattern:

```typescript
import ky from 'ky';

const api = ky.create({
  prefixUrl: import.meta.env.VITE_API_URL,
  hooks: {
    beforeRequest: [
      request => {
        const token = localStorage.getItem('token');
        if (token) {
          request.headers.set('Authorization', `Bearer ${token}`);
        }
      }
    ]
  }
});
```

### 4. State Management
- Use Svelte stores for global state
- Use `$state` runes for component-local state
- Consider Tanstack Query for server state

### 5. Testing
- Add unit tests for business logic
- Add integration tests for API interactions
- Use Playwright for E2E tests

## Timeline Estimate

### Immediate (Next 2 weeks)
- Phase 1: Core Features (File handling, Calendar, Forms)
- **Total: 5-8 days**

### Short Term (1-2 months)
- Phase 2: Help Center Module
- Phase 3: Contact Management Module
- Phase 4: Conversation Module (critical)
- Phase 7: Filter Module
- **Total: 20-28 days**

### Medium Term (2-4 months)
- Phase 5: Captain AI Module
- Phase 6: Assignment Policy Module
- Phase 8: Additional Features
- **Total: 14-19 days**

### Grand Total
**39-55 working days** (approximately 8-11 weeks with full-time development)

## Migration Strategy

### Option 1: Incremental Migration (Recommended)
- Build Svelte components alongside existing Vue components
- Gradually replace Vue components with Svelte equivalents
- Maintain backward compatibility during transition
- Test extensively in staging before production

### Option 2: Module-by-Module
- Complete one entire module at a time
- Switch module from Vue to Svelte all at once
- Lower risk per module
- Easier to test and validate

### Option 3: Page-by-Page
- Migrate complete pages to Svelte
- Use routing to direct to Svelte pages
- Cleanest separation
- Easiest rollback if issues occur

## Dependencies to Add

### Production
```json
{
  "@tanstack/svelte-query": "^5.0.0",  // Server state management
  "tiptap": "^2.0.0",                   // Rich text editor
  "zod": "^3.23.0",                     // Already added
  "sveltekit-superforms": "^2.0.0",     // Already added
  "vaul-svelte": "^1.0.0-next.7",       // Already added (drawer)
  "embla-carousel-svelte": "^8.0.0",    // Already added
  "date-fns": "^3.0.0",                 // Date utilities
  "socket.io-client": "^4.7.0",         // Real-time updates
  "papaparse": "^5.4.1"                 // CSV parsing
}
```

### Development
```json
{
  "@testing-library/svelte": "^4.0.0",
  "@playwright/test": "^1.40.0",
  "vitest": "^1.0.0"
}
```

## Success Metrics

### Code Quality
- [ ] All components have Histoire stories
- [ ] All components have TypeScript types
- [ ] All components follow Tailwind-only styling
- [ ] All components use Composition API with `<script setup>`

### Performance
- [ ] Initial page load < 3s
- [ ] Component render time < 100ms
- [ ] Bundle size < 500KB (gzipped)

### User Experience
- [ ] All features from Vue version available
- [ ] No regressions in functionality
- [ ] Improved performance over Vue version
- [ ] Better accessibility scores

## Next Steps

1. **Review and approve this plan** with the team
2. **Set up project board** with issues for each component
3. **Prioritize Phase 1** components for immediate implementation
4. **Assign developers** to each phase
5. **Establish code review process** for Svelte components
6. **Set up CI/CD pipeline** for Svelte builds
7. **Create staging environment** for Svelte UI testing

---

**Document Created:** December 28, 2024
**Status:** Planning Phase
**Next Review:** After Phase 1 completion
