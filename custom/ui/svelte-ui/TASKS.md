# Svelte UI Migration Tasks

## Overview
This document outlines the step-by-step tasks for migrating all Vue components to Svelte, organized by priority and module.

**Current Status:** 69/69 primitives complete | 4/52 application features complete

---

## Phase 1: Complete Primitives (HIGH PRIORITY) 🔴 ✅ COMPLETE

### Task 1.1: Calendar Component ✅
- [x] Add calendar component using shadcn-svelte
- [x] Create Calendar.story.svelte with variants:
  - Default calendar
  - Date selection
  - Range selection
  - Disabled dates
  - Min/max dates
  - Multiple months
- [x] Test calendar functionality
- [x] Update README.md to mark as complete

**Status:** COMPLETE

### Task 1.2: Date Picker Component ✅
- [x] Create date-picker component directory
- [x] Implement date-picker.svelte using Calendar + Popover
- [x] Add index.ts with exports
- [x] Create DatePicker.story.svelte with variants:
  - Single date picker
  - Date range picker
  - With time picker
  - With presets (Today, Yesterday, Last 7 days, etc.)
  - Disabled state
  - Different formats
- [x] Test date picker functionality
- [x] Update README.md to mark as complete

**Status:** COMPLETE

### Task 1.3: Form Validation ✅
- [x] Install/configure Formsnap if not already present
- [x] Create form validation examples
- [x] Create Form.story.svelte with variants:
  - Basic form validation
  - Complex form with multiple fields
  - Async validation
  - Custom error messages
  - Form submission handling
- [x] Document form validation patterns
- [x] Update README.md to mark as complete

**Status:** COMPLETE

**Phase 1 Total:** 14-20 hours (2-3 days) ✅ COMPLETE

---

## Phase 2: File ## Phase 2: File & Media Handling (HIGH PRIORITY) 🔴 Media Handling (HIGH PRIORITY) 🔴 ✅ COMPLETE

### Task 2.1: File Upload Component ✅
- [ ] Create file-upload component directory
- [ ] Implement file-upload.svelte with:
  - Drag and drop area
  - File browsing
  - Multiple file support
  - File type restrictions
  - File size limits
  - Preview for images
  - Progress indicators
  - Remove file capability
- [ ] Create FileUpload.story.svelte with variants:
  - Single file upload
  - Multiple file upload
  - Image preview
  - Different file types
  - Upload progress
  - Error states
- [ ] Test file upload functionality
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 2.2: Audio Recorder Component ✅
- [ ] Create audio-recorder component directory
- [ ] Implement audio-recorder.svelte with:
  - Record button
  - Recording indicator
  - Waveform visualization
  - Playback controls
  - Duration display
  - Stop/cancel recording
  - Save recording
- [ ] Create AudioRecorder.story.svelte with variants:
  - Default recorder
  - Recording state
  - Playback state
  - Permission denied state
- [ ] Test audio recording functionality
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 2.3: Emoji Picker Component ✅
- [ ] Create emoji-picker component directory
- [ ] Implement emoji-picker.svelte with:
  - Emoji categories
  - Search functionality
  - Recent emojis
  - Skin tone selector
  - Keyboard navigation
- [ ] Create EmojiPicker.story.svelte with variants:
  - Default picker
  - With search
  - Recent emojis
  - Different categories
- [ ] Test emoji picker functionality
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 2.4: Image Gallery Component ✅
- [ ] Create image-gallery component directory
- [ ] Implement image-gallery.svelte with:
  - Grid layout
  - Lightbox view
  - Navigation controls
  - Zoom functionality
  - Thumbnails
- [ ] Create ImageGallery.story.svelte
- [ ] Test image gallery
- [ ] Update README.md

**Estimated Time:** 6-8 hours

**Phase 2 Total:** 32-40 hours (4-5 days) ✅ COMPLETE

---

## Phase 3: Help Center Module (MEDIUM PRIORITY) 🟡

### Task 3.1: ArticlesPage Component
- [ ] Examine Vue component: `app/javascript/dashboard/components-next/HelpCenter/Pages/ArticlePage/ArticlesPage.vue`
- [ ] Create articles-page directory in custom/ui/svelte-ui
- [ ] Implement articles-page.svelte with:
  - Article list view
  - Search/filter bar
  - Sort options
  - Pagination
  - Empty state
  - Loading state
- [ ] Create ArticlesPage.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 3.2: CategoriesPage Component
- [ ] Examine Vue component: `CategoriesPage.vue`
- [ ] Create categories-page directory
- [ ] Implement categories-page.svelte with:
  - Category grid/list
  - Add/edit category
  - Delete category
  - Reorder categories
- [ ] Create CategoriesPage.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 3.3: LocalesPage Component
- [ ] Examine Vue component: `LocalesPage.vue`
- [ ] Create locales-page directory
- [ ] Implement locales-page.svelte with:
  - Locale list
  - Add/remove locales
  - Default locale indicator
  - Translation status
- [ ] Create LocalesPage.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 3.4: PortalSettings Component
- [ ] Examine Vue component: `PortalSettings.vue`
- [ ] Create portal-settings directory
- [ ] Implement portal-settings.svelte with:
  - Portal configuration form
  - SEO settings
  - Custom domain
  - Theme customization
- [ ] Create PortalSettings.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 3.5: Article Editor Component
- [ ] Examine Vue component: `ArticleEditor.vue`
- [ ] Install TipTap or rich text editor
- [ ] Create article-editor directory
- [ ] Implement article-editor.svelte with:
  - Rich text editing
  - Markdown support
  - Code blocks
  - Image insertion
  - Link insertion
  - Preview mode
- [ ] Create ArticleEditor.story.svelte
- [ ] Update README.md

**Estimated Time:** 12-16 hours

### Task 3.6: Empty States
- [ ] Create article-empty-state component
- [ ] Create portal-empty-state component
- [ ] Create stories for both
- [ ] Update README.md

**Estimated Time:** 4-6 hours

**Phase 3 Total:** 46-60 hours (6-8 days)

---

## Phase 4: Contact Management Module (MEDIUM PRIORITY) 🟡

### Task 4.1: Contact Merge Form
- [ ] Examine Vue component: `ContactMergeForm.vue`
- [ ] Create contact-merge-form directory
- [ ] Implement contact-merge-form.svelte with:
  - Side-by-side comparison
  - Field selection
  - Merge preview
  - Confirm merge
- [ ] Create ContactMergeForm.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 4.2: Contact Import/Export
- [ ] Examine Vue components: `ContactImportDialog.vue`, `ContactExportDialog.vue`
- [ ] Install CSV parsing library (papaparse)
- [ ] Create contact-import directory
- [ ] Implement contact-import.svelte with:
  - File upload
  - CSV parsing
  - Field mapping
  - Validation
  - Import progress
- [ ] Create contact-export directory
- [ ] Implement contact-export.svelte with:
  - Export format selection
  - Field selection
  - Download generation
- [ ] Create stories for both
- [ ] Update README.md

**Estimated Time:** 12-14 hours

### Task 4.3: Contact CRUD Dialog
- [ ] Examine Vue component: `CreateNewContactDialog.vue`
- [ ] Create contact-dialog directory
- [ ] Implement contact-dialog.svelte with:
  - Create mode
  - Edit mode
  - Form validation
  - Custom fields
  - Avatar upload
- [ ] Create ContactDialog.story.svelte
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 4.4: Contact Labels Management
- [ ] Create contact-labels directory
- [ ] Implement contact-labels.svelte with:
  - Label list
  - Add/remove labels
  - Label colors
  - Search labels
- [ ] Create ContactLabels.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 4.5: Contact Segments
- [ ] Create contact-segments directory
- [ ] Implement contact-segments.svelte with:
  - Segment builder
  - Conditions
  - Preview count
  - Save segment
- [ ] Create ContactSegments.story.svelte
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 4.6: Contact List & Details
- [ ] Create contact-list directory
- [ ] Create contact-details-layout directory
- [ ] Implement both components
- [ ] Create stories
- [ ] Update README.md

**Estimated Time:** 12-14 hours

**Phase 4 Total:** 58-70 hours (7-9 days)

---

## Phase 5: Conversation Module (HIGH PRIORITY) 🔴

### Task 5.1: Conversation List
- [ ] Examine Vue components in `Conversation/`
- [ ] Create conversation-list directory
- [ ] Implement conversation-list.svelte with:
  - Conversation items
  - Unread indicators
  - Last message preview
  - Timestamp
  - Avatars
  - Status indicators
  - Real-time updates
- [ ] Create ConversationList.story.svelte
- [ ] Update README.md

**Estimated Time:** 12-14 hours

### Task 5.2: Conversation Details
- [ ] Create conversation-details directory
- [ ] Implement conversation-details.svelte with:
  - Header with contact info
  - Message thread
  - Reply box integration
  - Attachment display
  - Metadata sidebar
- [ ] Create ConversationDetails.story.svelte
- [ ] Update README.md

**Estimated Time:** 14-16 hours

### Task 5.3: Message Thread
- [ ] Create message-thread directory
- [ ] Implement message-thread.svelte with:
  - Message list
  - Infinite scroll
  - Date separators
  - System messages
  - Loading states
- [ ] Create MessageThread.story.svelte
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 5.4: SLA & Priority Components
- [ ] Create sla-card-label component
- [ ] Create priority-icon component
- [ ] Implement both with proper styling
- [ ] Create stories
- [ ] Update README.md

**Estimated Time:** 4-6 hours

### Task 5.5: Conversation Filters
- [ ] Create conversation-filters directory
- [ ] Implement filters for:
  - Status
  - Assignee
  - Labels
  - Inbox
  - Date range
- [ ] Create ConversationFilters.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

**Phase 5 Total:** 48-58 hours (6-7 days)

---

## Phase 6: Filter Module (MEDIUM PRIORITY) 🟡

### Task 6.1: Active Filter Preview
- [ ] Examine Vue component: `ActiveFilterPreview.vue`
- [ ] Create active-filter-preview directory
- [ ] Implement active-filter-preview.svelte with:
  - Filter chips
  - Remove filter
  - Clear all
- [ ] Create ActiveFilterPreview.story.svelte
- [ ] Update README.md

**Estimated Time:** 4-6 hours

### Task 6.2: Condition Row
- [ ] Examine Vue component: `ConditionRow.vue`
- [ ] Create condition-row directory
- [ ] Implement condition-row.svelte with:
  - Attribute selector
  - Operator selector
  - Value input
  - Add/remove condition
- [ ] Create ConditionRow.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 6.3: Filter Select Components
- [ ] Examine Vue components: `FilterSelect.vue`, `MultiSelect.vue`, `SingleSelect.vue`
- [ ] Create filter-select directory
- [ ] Create multi-select-filter directory
- [ ] Create single-select-filter directory
- [ ] Implement all three components
- [ ] Create stories for each
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 6.4: Filter Save/Load
- [ ] Create filter-presets directory
- [ ] Implement save filter functionality
- [ ] Implement load filter functionality
- [ ] Create FilterPresets.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

**Phase 6 Total:** 26-34 hours (3-4 days)

---

## Phase 7: Captain AI Module (LOW PRIORITY) 🟢

### Task 7.1: Add New Rules Dialog
- [ ] Examine Vue component: `AddNewRulesDialog.vue`
- [ ] Create add-new-rules-dialog directory
- [ ] Implement add-new-rules-dialog.svelte
- [ ] Create AddNewRulesDialog.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 7.2: Document Upload Card
- [ ] Examine Vue component: `DocumentCard.vue`
- [ ] Create document-upload-card directory (note: document-card already exists)
- [ ] Implement document-upload.svelte
- [ ] Create DocumentUpload.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 7.3: Response Suggestions Card
- [ ] Examine Vue component: `ResponseCard.vue`
- [ ] Create response-suggestions directory
- [ ] Implement response-suggestions.svelte
- [ ] Create ResponseSuggestions.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 7.4: Rule Management & Scenarios
- [ ] Examine Vue components: `RuleCard.vue`, `ScenariosCard.vue`
- [ ] Create rule-management directory
- [ ] Create scenarios-card directory (if different from existing)
- [ ] Implement both components
- [ ] Create stories
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 7.5: AI Settings & Tools
- [ ] Examine Vue components: `SettingsHeader.vue`, `ToolsDropdown.vue`
- [ ] Create ai-settings directory
- [ ] Create tools-dropdown directory
- [ ] Implement both components
- [ ] Create stories
- [ ] Update README.md

**Estimated Time:** 8-10 hours

**Phase 7 Total:** 36-46 hours (5-6 days)

---

## Phase 8: Assignment Policy Module (LOW PRIORITY) 🟢

### Task 8.1: Agent Capacity Policy
- [ ] Examine Vue component: `AgentCapacityPolicyCard.vue`
- [ ] Note: assignment-policy already exists, check if it covers this
- [ ] If needed, create agent-capacity-policy directory
- [ ] Implement agent-capacity-policy.svelte
- [ ] Create AgentCapacityPolicy.story.svelte
- [ ] Update README.md

**Estimated Time:** 6-8 hours

### Task 8.2: Assignment Policy Card
- [ ] Examine Vue component: `AssignmentPolicyCard.vue`
- [ ] Verify existing assignment-policy component
- [ ] Enhance if needed
- [ ] Update story if needed
- [ ] Update README.md

**Estimated Time:** 4-6 hours

### Task 8.3: Assignment Card & Data Table
- [ ] Examine Vue components: `AssignmentCard.vue`, `DataTable.vue`
- [ ] Create assignment-card directory (if not exists)
- [ ] Create policy-data-table directory
- [ ] Implement both components
- [ ] Create stories
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 8.4: Policy Rules
- [ ] Examine Vue components: `ExclusionRules.vue`, `FairDistribution.vue`, `InboxCapacityLimits.vue`
- [ ] Create exclusion-rules directory
- [ ] Create fair-distribution directory
- [ ] Create inbox-capacity-limits directory
- [ ] Implement all three components
- [ ] Create stories
- [ ] Update README.md

**Estimated Time:** 12-14 hours

**Phase 8 Total:** 30-38 hours (4-5 days)

---

## Phase 9: Additional Features (LOW PRIORITY) 🟢

### Task 9.1: Notification Center
- [ ] Create notification-center directory
- [ ] Implement notification-center.svelte with:
  - Notification list
  - Mark as read
  - Clear all
  - Categories
- [ ] Create NotificationCenter.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 9.2: Search Component
- [ ] Create global-search directory
- [ ] Implement global-search.svelte with:
  - Search input
  - Results dropdown
  - Category filters
  - Keyboard navigation
- [ ] Create GlobalSearch.story.svelte
- [ ] Update README.md

**Estimated Time:** 8-10 hours

### Task 9.3: User Menu
- [ ] Create user-menu directory
- [ ] Implement user-menu.svelte with:
  - User profile display
  - Status selector
  - Settings link
  - Logout
- [ ] Create UserMenu.story.svelte
- [ ] Update README.md

**Estimated Time:** 4-6 hours

### Task 9.4: Settings Panel
- [ ] Create settings-panel directory
- [ ] Implement settings-panel.svelte with:
  - Navigation tabs
  - Settings sections
  - Save/cancel
- [ ] Create SettingsPanel.story.svelte
- [ ] Update README.md

**Estimated Time:** 10-12 hours

### Task 9.5: Report Dashboard
- [ ] Create report-dashboard directory
- [ ] Install chart library (layerchart)
- [ ] Implement report-dashboard.svelte with:
  - Charts
  - Metrics cards
  - Date range selector
  - Export reports
- [ ] Create ReportDashboard.story.svelte
- [ ] Update README.md

**Estimated Time:** 12-14 hours

### Task 9.6: Team Management
- [ ] Create team-management directory
- [ ] Implement team-management.svelte with:
  - Team member list
  - Add/remove members
  - Role management
  - Permissions
- [ ] Create TeamManagement.story.svelte
- [ ] Update README.md

**Estimated Time:** 10-12 hours

**Phase 9 Total:** 52-64 hours (7-8 days)

---

## Summary

### Total Effort Estimate
- **Phase 1:** 14-20 hours (2-3 days) - COMPLETE PRIMITIVES ✅
- **Phase 2:** 32-40 hours (4-5 days) - FILE HANDLING ✅
- **Phase 3:** 46-60 hours (6-8 days) - HELP CENTER
- **Phase 4:** 58-70 hours (7-9 days) - CONTACTS
- **Phase 5:** 48-58 hours (6-7 days) - CONVERSATIONS
- **Phase 6:** 26-34 hours (3-4 days) - FILTERS
- **Phase 7:** 36-46 hours (5-6 days) - CAPTAIN AI
- **Phase 8:** 30-38 hours (4-5 days) - ASSIGNMENT POLICY
- **Phase 9:** 52-64 hours (7-8 days) - ADDITIONAL FEATURES

**Grand Total:** 342-430 hours (43-54 working days or 8.5-10.8 weeks)
**Completed:** 46-60 hours (Phase 1 + Phase 2)
**Remaining:** 296-370 hours (37-46 working days or 7.4-9.2 weeks)

### Progress Tracking

#### Primitives: 69/69 Complete (100%) ✅
- [x] Button, Input, Checkbox, Switch, Textarea, Label, Separator
- [x] Badge, Avatar, Card, Spinner
- [x] Dialog, Dropdown Menu, Select, Popover, Tooltip
- [x] Tabs, Accordion, Alert, Toast, Sidebar, Table, Command
- [x] And 40+ more primitives
- [x] Calendar ⭐ NEW
- [x] Date Picker ⭐ NEW
- [x] Form Validation ⭐ NEW

#### Chatwoot-Specific: 26/26 Complete (100%) ✅
- [x] Message Bubble, Conversation Card, Contact Card, Reply Box
- [x] And 22 more Chatwoot-specific components

#### Application Features: 4/52 Complete (7.7%)
- [x] File Upload ⭐ NEW
- [x] Audio Recorder ⭐ NEW
- [x] Emoji Picker ⭐ NEW
- [x] Image Gallery ⭐ NEW
- [ ] 8 Help Center components
- [ ] 8 Contact Management components
- [ ] 7 Conversation components
- [ ] 6 Filter components
- [ ] 7 Captain AI components
- [ ] 6 Assignment Policy components
- [ ] 6 Additional features

### Priority Order for Implementation
1. **Phase 1** - Complete primitives (required for other components) ✅
2. **Phase 2** - File handling (essential for messaging) ✅
3. **Phase 5** - Conversations (core feature)
4. **Phase 4** - Contacts (core feature)
5. **Phase 3** - Help Center (important feature)
6. **Phase 6** - Filters (supporting feature)
7. **Phase 7** - Captain AI (optional feature)
8. **Phase 8** - Assignment Policy (optional feature)
9. **Phase 9** - Additional features (nice to have)

### Next Steps
1. Begin with Phase 1 to complete the remaining primitives
2. Set up necessary dependencies (TipTap, CSV parser, etc.)
3. Work through phases sequentially
4. Test thoroughly after each phase
5. Update documentation continuously
6. Create PRs per phase for easier review

---

**Document Created:** December 28, 2024
**Status:** Ready for implementation
**Current Phase:** Phase 1 - Complete Primitives
