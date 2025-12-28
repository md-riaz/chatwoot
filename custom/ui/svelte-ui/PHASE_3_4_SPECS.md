# Phase 3 & 4: Component Specifications

## Important Note

Phases 3 and 4 together involve **16 complex application components** with an estimated implementation time of **104-130 hours (13-16 working days)**. 

This document provides detailed specifications for all components to enable systematic implementation in future sessions.

## Phase 3: Help Center Module (8 Components)

### Status: PLANNED - Implementation in Progress

### 3.1 Articles Page Component
**Purpose:** Display and manage help center articles
**Complexity:** High
**Estimated Time:** 6-8 hours

**Key Features:**
- Article list with search and filters
- Sort by date, popularity, category
- Pagination
- Quick actions (edit, delete, publish)
- Bulk operations
- Article preview

**Vue Reference:** `app/javascript/dashboard/components-next/HelpCenter/Pages/ArticlePage/ArticlesPage.vue`

**Props:**
```typescript
{
  articles: Article[];
  categories: Category[];
  onArticleSelect: (id: string) => void;
  onArticleCreate: () => void;
  onArticleDelete: (id: string) => void;
}
```

### 3.2 Article Editor Component
**Purpose:** Rich text editor for creating/editing articles
**Complexity:** Very High
**Estimated Time:** 8-10 hours

**Key Features:**
- Rich text editing (TipTap integration)
- Image upload
- Code blocks
- Markdown support
- Auto-save drafts
- SEO metadata
- Publish/draft states

**Vue Reference:** `app/javascript/dashboard/components-next/HelpCenter/Pages/ArticlePage/ArticleEditor.vue`

### 3.3 Categories Component
**Purpose:** Manage article categories
**Complexity:** Medium
**Estimated Time:** 4-6 hours

**Key Features:**
- Category tree view
- Drag-and-drop reordering
- Add/edit/delete categories
- Icon selection
- Locale support

### 3.4 Locales Component
**Purpose:** Manage help center localizations
**Complexity:** Medium
**Estimated Time:** 5-6 hours

**Key Features:**
- Locale list
- Add/remove locales
- Set default locale
- Translation status per article
- Locale switcher

### 3.5 Portal Configuration
**Purpose:** Configure help center portal settings
**Complexity:** High
**Estimated Time:** 6-8 hours

**Key Features:**
- Portal URL configuration
- Branding (colors, logo)
- Header/footer customization
- SEO settings
- Analytics integration

### 3.6 Settings Component
**Purpose:** General help center settings
**Complexity:** Medium
**Estimated Time:** 4-5 hours

**Key Features:**
- Visibility settings
- Authentication options
- Search configuration
- Contact form settings

### 3.7 Sidebar Navigation
**Purpose:** Help center navigation sidebar
**Complexity:** Low
**Estimated Time:** 3-4 hours

**Key Features:**
- Category navigation
- Recent articles
- Popular articles
- Search integration

### 3.8 Search Component
**Purpose:** Help center article search
**Complexity:** Medium
**Estimated Time:** 5-6 hours

**Key Features:**
- Full-text search
- Search suggestions
- Recent searches
- Filter by category
- Search results highlighting

---

## Phase 4: Contact Management (8 Components)

### Status: PLANNED - Implementation in Progress

### 4.1 Contact List Component
**Purpose:** Display and manage contacts
**Complexity:** High
**Estimated Time:** 7-9 hours

**Key Features:**
- Contacts table/grid view
- Search and filters
- Sort by multiple columns
- Bulk actions
- Export contacts
- Pagination
- Contact quick view

**Vue Reference:** `app/javascript/dashboard/components-next/Contacts/ContactsList.vue`

**Props:**
```typescript
{
  contacts: Contact[];
  filters: FilterOptions;
  onContactSelect: (id: string) => void;
  onContactCreate: () => void;
  onBulkAction: (action: string, ids: string[]) => void;
}
```

### 4.2 Contact Details Component
**Purpose:** Display detailed contact information
**Complexity:** High
**Estimated Time:** 8-10 hours

**Key Features:**
- Contact profile display
- Custom attributes
- Conversation history
- Activity timeline
- Social profiles
- Contact tags
- Edit inline
- Notes section

**Vue Reference:** `app/javascript/dashboard/components-next/Contacts/ContactDetails.vue`

### 4.3 Contact Form Component
**Purpose:** Create/edit contact information
**Complexity:** Medium-High
**Estimated Time:** 6-8 hours

**Key Features:**
- Contact information fields
- Custom attributes
- Social profiles
- Validation
- Avatar upload
- Tag selection
- Company association

### 4.4 Contact Notes Component
**Purpose:** Add and manage contact notes
**Complexity:** Medium
**Estimated Time:** 4-5 hours

**Key Features:**
- Note list
- Add/edit/delete notes
- Rich text support
- @mentions
- Note timestamps
- Private notes

### 4.5 Contact Activities Component
**Purpose:** Display contact activity timeline
**Complexity:** Medium
**Estimated Time:** 5-6 hours

**Key Features:**
- Activity timeline
- Activity types (calls, emails, conversations)
- Filter by activity type
- Activity details
- Date grouping

### 4.6 Contact Merge Component
**Purpose:** Merge duplicate contacts
**Complexity:** High
**Estimated Time:** 8-10 hours

**Key Features:**
- Duplicate detection
- Side-by-side comparison
- Field selection for merge
- Merge preview
- Undo merge
- Bulk merge

### 4.7 Contact Filter Component
**Purpose:** Advanced contact filtering
**Complexity:** High
**Estimated Time:** 7-9 hours

**Key Features:**
- Filter builder
- Multiple filter conditions
- Save filter presets
- Dynamic filters
- Filter by custom attributes
- Date range filters

### 4.8 Contact Import Component
**Purpose:** Import contacts from CSV/other sources
**Complexity:** High
**Estimated Time:** 8-10 hours

**Key Features:**
- File upload (CSV, Excel)
- Column mapping
- Preview import
- Validation
- Duplicate detection
- Import progress
- Error handling

---

## Implementation Strategy

### Immediate Next Steps (This Session)
1. ✅ Create directory structure
2. ✅ Document all component specifications
3. 🔄 Implement 2-3 core components as foundation:
   - Contact List (highest priority)
   - Article List (highest priority)
   - Contact Form (commonly used)

### Future Sessions
- **Session 2:** Complete remaining Phase 3 components (6 components)
- **Session 3:** Complete remaining Phase 4 components (5 components)
- **Session 4:** Integration testing and refinements

### Prioritization
**Must Have (Core):**
- Contact List, Contact Details, Contact Form
- Article List, Article Editor

**Should Have (Important):**
- Contact Filter, Contact Notes
- Portal Configuration, Categories

**Nice to Have (Enhancement):**
- Contact Import, Contact Merge
- Search, Sidebar, Locales

---

## Dependencies

### External Libraries Needed
- **Rich Text Editor:** TipTap or similar
- **Drag & Drop:** @dnd-kit/core
- **CSV Parsing:** papa-parse
- **Date Handling:** date-fns (already available)

### Internal Dependencies
- Dialog, Form, Table components (✅ Available)
- File Upload component (✅ Available - Phase 2)
- Avatar, Badge components (✅ Available)
- Command palette for search (✅ Available)

---

## Testing Requirements

Each component should include:
- Unit tests for business logic
- Component tests for user interactions
- Histoire stories for visual testing
- Accessibility tests

---

## Notes for Implementation

1. **Reusable Patterns:** Many components share similar patterns (lists, forms, filters)
2. **API Integration:** Components should accept data as props initially, with API integration added later
3. **Responsive Design:** All components must work on mobile/tablet/desktop
4. **Accessibility:** WCAG 2.1 AA compliance required
5. **I18n:** All text should be internationalized

---

## Estimated Completion Timeline

- **Phase 3 Core (3 components):** 18-24 hours
- **Phase 3 Remaining (5 components):** 28-36 hours
- **Phase 4 Core (3 components):** 21-27 hours
- **Phase 4 Remaining (5 components):** 37-43 hours

**Total:** 104-130 hours (13-16 working days)

---

*Last Updated: December 28, 2024*
*Status: Specifications Complete - Implementation in Progress*
