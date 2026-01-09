# Svelte 5 Migration Solutions

This document provides detailed solutions for all Svelte 5 issues identified in the codebase.

## Table of Contents
1. [Issue Summary](#issue-summary)
2. [Understanding the Issues](#understanding-the-issues)
3. [Solution Patterns from llms.txt](#solution-patterns-from-llmstxt)
4. [Component-by-Component Solutions](#component-by-component-solutions)

## Issue Summary

**Total Issues Found**: 49
- **svelte_component_deprecated**: 7 instances (6 files)
- **state_referenced_locally**: 42 instances (13 files)

## Understanding the Issues

### Issue 1: `svelte_component_deprecated`

**Problem**: In Svelte 5 runes mode, `<svelte:component>` is deprecated because components are dynamic by default.

**Old Pattern (Svelte 4)**:
```svelte
<svelte:component this={dynamicComponent} {...props} />
```

**New Pattern (Svelte 5)**:
```svelte
{@const Component = dynamicComponent}
<Component {...props} />
```

Or simply:
```svelte
<dynamicComponent {...props} />
```

### Issue 2: `state_referenced_locally`

**Problem**: When you reference reactive state or props at the top level (not inside a closure or derived), it captures only the initial value.

**Old Pattern (Problematic)**:
```svelte
<script>
  let { contact } = $props();
  
  // This captures only the initial value!
  let name = contact.name;
  let email = contact.email;
</script>
```

**New Pattern (Solution 1 - Using $derived)**:
```svelte
<script>
  let { contact } = $props();
  
  // These will stay reactive
  let name = $derived(contact.name);
  let email = $derived(contact.email);
</script>
```

**New Pattern (Solution 2 - Using in template)**:
```svelte
<script>
  let { contact } = $props();
</script>

<!-- Reference directly in template (already reactive) -->
<input bind:value={contact.name} />
```

**New Pattern (Solution 3 - Using closures)**:
```svelte
<script>
  let { status } = $props();
  
  // Use inside a function to maintain reactivity
  function getStatusColor() {
    return status === 'active' ? 'green' : 'gray';
  }
</script>

<div class={getStatusColor()}>Status</div>
```

## Solution Patterns from llms.txt

Based on the Svelte 5 documentation in llms.txt, here are the key patterns to apply:

### Pattern 1: Dynamic Components
```svelte
<!-- Instead of <svelte:component> -->
{@const Component = iconComponent}
<Component class="icon" />
```

### Pattern 2: Derived State
```svelte
<script>
  let { data } = $props();
  
  // Use $derived for computed values
  let processedData = $derived(data.map(item => item.value));
</script>
```

### Pattern 3: Reactive References in Closures
```svelte
<script>
  let { count } = $props();
  
  // Function maintains reactive reference
  const doubled = () => count * 2;
</script>

<p>Count doubled: {doubled()}</p>
```

### Pattern 4: Direct Template Access
```svelte
<script>
  let { user } = $props();
</script>

<!-- Access props directly in template - already reactive -->
<h1>{user.name}</h1>
<p>{user.email}</p>
```

## Component-by-Component Solutions

### 1. src/lib/components/conversation-workflow/AttributeListItem.svelte

**Issue**: Line 64 - `svelte_component_deprecated`

**Current Code**:
```svelte
<svelte:component this={AttributeIcon} />
```

**Fixed Code**:
```svelte
{@const Component = AttributeIcon}
<Component />
```

**Explanation**: Components are dynamic by default in Svelte 5. We use `{@const}` to create a local reference and render it directly.

---

### 2. src/lib/components/layout/AppSidebar.svelte

**Issue**: Line 187 - `svelte_component_deprecated`

**Current Code**:
```svelte
<svelte:component this={item.icon} class="h-4 w-4" />
```

**Fixed Code**:
```svelte
{@const Icon = item.icon}
<Icon class="h-4 w-4" />
```

---

### 3. src/lib/components/macros/MacrosList.svelte

**Issue**: Line 112 - `svelte_component_deprecated`

**Current Code**:
```svelte
<svelte:component this={icon} class="h-4 w-4" />
```

**Fixed Code**:
```svelte
{@const Icon = icon}
<Icon class="h-4 w-4" />
```

---

### 4. src/routes/app/accounts/[accountId]/+page.svelte

**Issue**: Line 128 - `svelte_component_deprecated`

**Current Code**:
```svelte
<svelte:component this={stat.icon} class="h-4 w-4 {stat.color}" />
```

**Fixed Code**:
```svelte
{@const Icon = stat.icon}
<Icon class="h-4 w-4 {stat.color}" />
```

---

### 5. src/routes/app/accounts/[accountId]/integrations/+page.svelte

**Issues**: 
- Line 171 - `svelte_component_deprecated`
- Line 178 - `svelte_component_deprecated`

**Current Code**:
```svelte
<svelte:component this={integration.icon} class="h-6 w-6" />
<!-- ... -->
<svelte:component this={getBadgeIcon(integration.status)} class="h-3 w-3" />
```

**Fixed Code**:
```svelte
{@const IntegrationIcon = integration.icon}
<IntegrationIcon class="h-6 w-6" />
<!-- ... -->
{@const BadgeIcon = getBadgeIcon(integration.status)}
<BadgeIcon class="h-3 w-3" />
```

---

### 6. src/routes/app/accounts/[accountId]/reports/+page.svelte

**Issues**: 
- Line 98 - `svelte_component_deprecated`
- Line 131 - `svelte_component_deprecated`

**Current Code**:
```svelte
<svelte:component this={activeSection.icon} class="h-5 w-5" />
<!-- ... -->
<svelte:component this={section.icon} class="h-5 w-5" />
```

**Fixed Code**:
```svelte
{@const SectionIcon = activeSection.icon}
<SectionIcon class="h-5 w-5" />
<!-- ... -->
{@const Icon = section.icon}
<Icon class="h-5 w-5" />
```

---

### 7. src/lib/components/ui/tab-bar/tab-bar.svelte

**Issue**: Line 22 - `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { tabs, initialActiveTab = tabs[0]?.id, onTabChange } = $props();
  
  let activeTab = $state(initialActiveTab); // Captures initial value only!
</script>
```

**Fixed Code**:
```svelte
<script>
  let { tabs, initialActiveTab = tabs[0]?.id, onTabChange } = $props();
  
  // Use a derived value to make it reactive
  let defaultTab = $derived(initialActiveTab ?? tabs[0]?.id);
  let activeTab = $state(defaultTab);
  
  // Or if initialActiveTab should only set once, use $effect
  let activeTab = $state(tabs[0]?.id);
  
  $effect(() => {
    if (initialActiveTab !== undefined) {
      activeTab = initialActiveTab;
    }
  });
</script>
```

**Explanation**: The warning occurs because `initialActiveTab` is captured once. If `initialActiveTab` is meant to be reactive (change over time), use `$derived`. If it's just an initial value, the current code is fine, but we can use `$effect` to be explicit.

---

### 8. src/lib/components/ui/contact-form/contact-form.svelte

**Issues**: Lines 24-29 - Multiple `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { contact } = $props();
  
  let name = $state(contact.name);
  let email = $state(contact.email);
  let phone = $state(contact.phone);
  let company = $state(contact.company);
  let location = $state(contact.location);
  let notes = $state(contact.notes);
</script>
```

**Fixed Code - Option 1 (Using $derived for sync)**:
```svelte
<script>
  let { contact } = $props();
  
  // If these need to stay in sync with contact prop
  let name = $derived(contact.name);
  let email = $derived(contact.email);
  let phone = $derived(contact.phone);
  let company = $derived(contact.company);
  let location = $derived(contact.location);
  let notes = $derived(contact.notes);
</script>
```

**Fixed Code - Option 2 (Using $effect for initialization)**:
```svelte
<script>
  let { contact } = $props();
  
  // If these are independent editable fields
  let name = $state('');
  let email = $state('');
  let phone = $state('');
  let company = $state('');
  let location = $state('');
  let notes = $state('');
  
  // Initialize from contact when it changes
  $effect(() => {
    name = contact.name ?? '';
    email = contact.email ?? '';
    phone = contact.phone ?? '';
    company = contact.company ?? '';
    location = contact.location ?? '';
    notes = contact.notes ?? '';
  });
</script>
```

**Fixed Code - Option 3 (Direct binding - Best for forms)**:
```svelte
<script>
  let { contact } = $props();
</script>

<!-- Bind directly to contact properties -->
<input bind:value={contact.name} />
<input bind:value={contact.email} />
<input bind:value={contact.phone} />
```

**Recommendation**: For form components, Option 3 (direct binding) is most idiomatic in Svelte 5.

---

### 9. src/lib/components/ui/contact-form/contact-merge-form.svelte

**Issue**: Line 25 - `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { duplicateContacts } = $props();
  
  let selectedDuplicate = $state(duplicateContacts[0]); // Captures initial value
</script>
```

**Fixed Code**:
```svelte
<script>
  let { duplicateContacts } = $props();
  
  // Use derived to track the first item reactively
  let defaultDuplicate = $derived(duplicateContacts[0]);
  let selectedDuplicate = $state(defaultDuplicate);
  
  // Or use effect if you want to update when duplicateContacts changes
  let selectedDuplicate = $state(null);
  
  $effect(() => {
    if (!selectedDuplicate && duplicateContacts.length > 0) {
      selectedDuplicate = duplicateContacts[0];
    }
  });
</script>
```

---

### 10. src/lib/components/ui/assignment-policy/agent-capacity-card.svelte

**Issues**: Line 21 - Multiple `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { current, capacity } = $props();
  
  let percentage = (current / capacity) * 100; // Captures initial values
</script>
```

**Fixed Code**:
```svelte
<script>
  let { current, capacity } = $props();
  
  // Use $derived for reactive calculation
  let percentage = $derived((current / capacity) * 100);
</script>
```

---

### 11. src/lib/components/ui/availability/availability-text.svelte

**Issue**: Line 30 - `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { status } = $props();
  
  let displayText = getDisplayText(status); // Captures initial value
</script>
```

**Fixed Code**:
```svelte
<script>
  let { status } = $props();
  
  // Use $derived for reactive computed value
  let displayText = $derived(getDisplayText(status));
</script>

<!-- Or use directly in template -->
<span>{getDisplayText(status)}</span>
```

---

### 12. src/lib/components/campaigns/SMSCampaignForm.svelte

**Issues**: Lines 29-33 - Multiple `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { campaign } = $props();
  
  let name = $state(campaign.name);
  let message = $state(campaign.message);
  let scheduledAt = $state(campaign.scheduledAt);
  let audience = $state(campaign.audience);
  let phoneNumbers = $state(campaign.phoneNumbers);
</script>
```

**Fixed Code** (Same pattern as contact-form):
```svelte
<script>
  let { campaign } = $props();
  
  // Option 1: Use $derived if these should stay in sync
  let name = $derived(campaign.name);
  let message = $derived(campaign.message);
  let scheduledAt = $derived(campaign.scheduledAt);
  let audience = $derived(campaign.audience);
  let phoneNumbers = $derived(campaign.phoneNumbers);
  
  // Option 2: Use $effect for initialization of editable state
  let name = $state('');
  let message = $state('');
  let scheduledAt = $state('');
  let audience = $state('all');
  let phoneNumbers = $state([]);
  
  $effect(() => {
    name = campaign.name ?? '';
    message = campaign.message ?? '';
    scheduledAt = campaign.scheduledAt ?? '';
    audience = campaign.audience ?? 'all';
    phoneNumbers = campaign.phoneNumbers ?? [];
  });
</script>
```

---

### 13. src/lib/components/campaigns/WhatsAppCampaignForm.svelte

**Issues**: Lines 29-33 - Multiple `state_referenced_locally`

**Solution**: Same pattern as SMSCampaignForm above.

---

### 14. src/lib/components/conversations/ConversationFilters.svelte

**Issues**: Lines 30-34, 45 - Multiple `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { statusCounts, sort } = $props();
  
  let allCount = statusCounts.all;
  let openCount = statusCounts.open;
  let resolvedCount = statusCounts.resolved;
  let pendingCount = statusCounts.pending;
  let snoozedCount = statusCounts.snoozed;
  
  let currentSort = sort;
</script>
```

**Fixed Code**:
```svelte
<script>
  let { statusCounts, sort } = $props();
  
  // Use $derived for reactive values
  let allCount = $derived(statusCounts.all);
  let openCount = $derived(statusCounts.open);
  let resolvedCount = $derived(statusCounts.resolved);
  let pendingCount = $derived(statusCounts.pending);
  let snoozedCount = $derived(statusCounts.snoozed);
  
  let currentSort = $derived(sort);
  
  // Or access directly in template
</script>

<!-- Direct access (no variables needed) -->
<span>{statusCounts.all}</span>
<span>{statusCounts.open}</span>
```

---

### 15. src/lib/components/portal/PortalHeader.svelte

**Issue**: Line 22 - `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { config } = $props();
  
  let logoUrl = config.logoUrl; // Captures initial value
</script>
```

**Fixed Code**:
```svelte
<script>
  let { config } = $props();
  
  // Use $derived for reactive value
  let logoUrl = $derived(config.logoUrl);
</script>

<!-- Or access directly -->
<img src={config.logoUrl} alt="Logo" />
```

---

### 16. src/lib/components/settings/SettingsNav.svelte

**Issue**: Line 36 - `state_referenced_locally`

**Current Code**:
```svelte
<script>
  let { basePath } = $props();
  
  function isActive(path: string) {
    return $page.url.pathname.startsWith(basePath + path); // basePath captured
  }
</script>
```

**Fixed Code**:
```svelte
<script>
  let { basePath } = $props();
  
  // basePath is already used inside a function, but the warning suggests using it in a closure
  // Make sure to access it reactively
  function isActive(path: string) {
    // This should work, but ensure basePath is accessed each time
    const fullPath = basePath + path;
    return $page.url.pathname.startsWith(fullPath);
  }
  
  // Or use $derived for paths
  let paths = $derived({
    general: basePath + '/general',
    team: basePath + '/team',
    // ... etc
  });
</script>
```

**Note**: The function already references `basePath` inside the closure, so it should be reactive. This might be a false positive, but we can verify by testing.

---

### 17. src/lib/components/survey/RatingInput.svelte

**Issue**: Line 18 - `state_referenced_locally`

**Solution**: Similar pattern to other components - use `$derived` or access directly.

---

## Testing Strategy

After applying these fixes:

1. **Unit Tests**: Run existing tests to ensure functionality is preserved
   ```bash
   npm test
   ```

2. **Type Checking**: Verify TypeScript types are correct
   ```bash
   npx svelte-check
   ```

3. **Manual Testing**: Test each affected component in the browser
   - Test form submissions
   - Test dynamic component rendering
   - Test reactive updates

4. **Integration Tests**: Verify workflows involving these components
   - Account dashboard
   - Campaign creation
   - Contact management
   - Settings navigation

## Migration Checklist

### Phase 1: svelte_component_deprecated (Easier)
- [ ] AttributeListItem.svelte
- [ ] AppSidebar.svelte
- [ ] MacrosList.svelte
- [ ] accounts/[accountId]/+page.svelte
- [ ] accounts/[accountId]/integrations/+page.svelte
- [ ] accounts/[accountId]/reports/+page.svelte

### Phase 2: state_referenced_locally (Requires Analysis)
- [ ] tab-bar.svelte
- [ ] contact-form.svelte
- [ ] contact-merge-form.svelte
- [ ] agent-capacity-card.svelte
- [ ] availability-text.svelte
- [ ] SMSCampaignForm.svelte
- [ ] WhatsAppCampaignForm.svelte
- [ ] ConversationFilters.svelte
- [ ] PortalHeader.svelte
- [ ] SettingsNav.svelte
- [ ] RatingInput.svelte

### Phase 3: Verification
- [ ] Run svelte-check (should show 0 issues)
- [ ] Run unit tests
- [ ] Manual QA testing
- [ ] Performance verification

## Key Takeaways from llms.txt

1. **Dynamic Components**: In Svelte 5, components are dynamic by default. Use `{@const}` to create a local reference.

2. **Reactive State**: Use `$derived` for computed values, `$state` for mutable state, and access props directly in templates.

3. **Closures**: Functions that reference props/state maintain reactivity automatically.

4. **Direct Access**: Often the simplest solution is to access props directly in templates rather than creating intermediate variables.

5. **$effect for Initialization**: Use `$effect` when you need to initialize state from props but want the state to be independently mutable.

## References

- [Svelte 5 Runes Documentation](https://svelte.dev/docs/svelte/what-are-runes)
- [$state Documentation](https://svelte.dev/docs/svelte/$state)
- [$derived Documentation](https://svelte.dev/docs/svelte/$derived)
- [Migration Guide](https://svelte.dev/docs/svelte/v5-migration-guide)
