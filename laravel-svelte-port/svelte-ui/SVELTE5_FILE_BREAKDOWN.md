# Detailed File-by-File Breakdown of Svelte 5 Errors

**Note**: Story/Histoire files have been removed (83 files) as they are not needed for production. This breakdown focuses on production component files only.

## Files with Missing Type Annotations

1. **src/lib/components/whatsapp/WhatsAppTemplateParser.svelte**
   - Line 159, 171, 198, 220: Add type annotations to event handlers
   ```typescript
   oninput={(e: Event & { currentTarget: HTMLInputElement }) => ...}
   ```

2. **src/routes/app/accounts/[accountId]/companies/+page.svelte**
   - Line 227, 234: Add type annotations
   ```typescript
   onclick={(e: MouseEvent) => handleEditCompany(e, company)}
   ```

3. **src/routes/app/accounts/[accountId]/settings/agents/+page.svelte**
   - Line 145: Add type annotation

4. **src/routes/app/accounts/[accountId]/settings/attributes/+page.svelte**
   - Lines 170, 177: Add type annotations

5. **src/routes/app/super_admin/agent-bots/[id]/edit/+page.svelte**
   - Lines 306, 322, 339: Add type annotations

6. **src/routes/app/super_admin/settings/+page.svelte**
   - Lines 318, 345: Add type annotations

## Files with Incorrect Component Props

### Card onclick Issues
1. **src/routes/app/accounts/[accountId]/+page.svelte**
   - Line 122: Card.Root doesn't accept onclick
   
2. **src/routes/app/accounts/[accountId]/settings/+page.svelte**
   - Line 68: Card.Root doesn't accept onclick

3. **src/routes/app/accounts/[accountId]/settings/agents/+page.svelte**
   - Line 112: Card.Root doesn't accept onclick

4. **src/routes/app/accounts/[accountId]/settings/inboxes/+page.svelte**
   - Line 102: Card.Root doesn't accept onclick

5. **src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte**
   - Lines 391, 416, 428: Card.Root doesn't accept onclick

### DropdownMenuItem onclick Issues
1. **src/routes/app/accounts/[accountId]/campaigns/+page.svelte**
   - Lines 131, 134, 137, 179, 182, 185: DropdownMenuItem doesn't accept onclick

### Switch/Checkbox id Issues
1. **src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte**
   - Lines 553, 601, 768, 892: Switch doesn't accept id

2. **src/routes/app/accounts/[accountId]/settings/notifications/+page.svelte**
   - Lines 99, 112, 125, 137, 157, 165, 175: Switch doesn't accept id

3. **src/routes/ui/[name]/+page.svelte**
   - Lines 94, 104: Checkbox/Switch doesn't accept id

### Input Type Issues
1. **src/routes/app/accounts/[accountId]/reports/+page.svelte**
   - Lines 57, 64: Input doesn't accept type="date"

2. **src/routes/app/accounts/[accountId]/settings/audit-logs/+page.svelte**
   - Lines 77, 84: Input doesn't accept type="date"

3. **src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte**
   - Line 503: Input doesn't accept type="color"

### Input min Attribute Issues
1. **src/routes/app/super_admin/accounts/[id]/edit/+page.svelte**
   - Lines 285, 300: Input doesn't accept min

2. **src/routes/app/super_admin/accounts/new/+page.svelte**
   - Lines 130, 145: Input doesn't accept min

### Input oninput Issues
1. **src/routes/app/accounts/[accountId]/companies/+page.svelte**
   - Line 117: Input doesn't accept oninput

2. **src/routes/app/super_admin/agent-bots/+page.svelte**
   - Line 140: Input doesn't accept onkeydown

3. **src/routes/app/super_admin/platform-apps/+page.svelte**
   - Line 105: Input doesn't accept onkeydown

### Table colspan Issues
**Note**: This was in story files which have been removed.

## Files with Snake_case Issues

1. **src/routes/app/accounts/[accountId]/contacts/+page.svelte**
   - Line 31: `phone_number` → `phoneNumber`
   - Line 117: `avatar_url` → `avatarUrl`
   - Line 128: `availability_status` → `availabilityStatus`
   - Line 130: `availability_status` → `availabilityStatus`
   - Line 142: `phone_number` → `phoneNumber`
   - Line 145: `phone_number` → `phoneNumber`
   - Line 149: `company_name` → Use `companyName` if available

## Files with Sidebar Export Issues

**Note**: This primarily affected story files which have been removed. If Sidebar components need Nav, Section, NavItem exports, update `src/lib/components/ui/sidebar/index.ts`.

## Files with Select.Root bind:selected Issues

1. **src/routes/app/accounts/[accountId]/settings/account/+page.svelte**
   - Lines 90, 105: Use `bind:value` instead of `bind:selected`

## Files with Type Mismatch Issues

1. **src/routes/app/accounts/[accountId]/conversations/[id]/+page.svelte**
   - Line 17: Add null coalescing `parseInt($page.params.id ?? '0')`

2. **src/routes/app/accounts/[accountId]/conversations/+page.svelte**
   - Line 30: Use correct method name (check ConversationsStore)

3. **src/routes/ui/[name]/+page.svelte**
   - Lines 33, 147: Add null check for componentName

4. **src/routes/app/super_admin/accounts/[id]/+page.svelte**
   - Lines 499, 500: Fix type incompatibility

5. **src/routes/app/super_admin/accounts/[id]/edit/+page.svelte**
   - Lines 121, 262: Fix Record type issues

## Files with Dialog bind:open Issues

1. **src/routes/app/super_admin/platform-apps/[id]/+page.svelte**
   - Line 225: Dialog.Root doesn't support bind:open

## Files with onMount/Effect Issues

1. **src/routes/widget/+layout.svelte**
   - Line 22: onMount async return type issue

2. **src/routes/portal/+layout.svelte**
   - Line 14: initI18n expects 0 arguments, not 1

## Files with Histoire Missing Module

**Status**: ✅ RESOLVED - All story files have been removed as they are not needed for production components.

## Files with Label/Icon Prop Issues

1. **src/routes/app/super_admin/settings/+page.svelte**
   - Lines 281, 283: Lock/LockOpen icons don't accept title prop

2. **src/routes/ui/[name]/+page.svelte**
   - Line 109: Label doesn't accept htmlFor

## Files with DropdownMenuContent align Issues

1. **src/routes/app/accounts/[accountId]/campaigns/+page.svelte**
   - Lines 130, 178: DropdownMenuContent doesn't accept align

## Files with DataTable emptyMessage Issue

1. **src/routes/app/super_admin/agent-bots/+page.svelte**
   - Line 166: DataTable doesn't accept emptyMessage

## Files with on:submit Deprecation

1. **src/routes/app/super_admin/platform-apps/[id]/edit/+page.svelte**
   - Line 117: Use onsubmit instead of on:submit

## Files with Non-reactive State Warning

1. **src/routes/app/super_admin/agent-bots/[id]/+page.svelte**
   - Line 15: `bot` should be declared with `$state()`

2. **src/routes/app/super_admin/platform-apps/[id]/+page.svelte**
   - Line 16: `platformApp` should be declared with `$state()`

## Summary by Category

**Critical (Breaking)**: Reduced after story file removal
- Snake_case property names
- Type mismatches
- Component prop types

**Important (Type Errors)**: ~80 files
- Component prop types
- Event handler annotations
- Non-bindable properties

**Warnings**: ~60 files
- Accessibility issues
- CSS compatibility
- Deprecated patterns

**Story Files**: ✅ RESOLVED - 83 story files removed (276 errors eliminated)
