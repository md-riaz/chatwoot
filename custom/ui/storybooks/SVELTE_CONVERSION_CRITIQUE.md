# Critique: Converting Chatwoot Histoire to Svelte 5 SvelteKit SPA

This document analyzes the feasibility and implications of converting Chatwoot's existing Histoire setup (Vue 3) to Svelte 5 with SvelteKit SPA mode.

## Executive Summary

**Feasibility:** ✅ Technically feasible, but requires significant effort  
**Recommended:** ⚠️ Only if migrating the entire frontend to Svelte

The Histoire library officially supports Svelte via `@histoire/plugin-svelte`, and the SvelteKit example at [histoire-dev/histoire/examples/sveltekit](https://github.com/histoire-dev/histoire/tree/main/examples/sveltekit) demonstrates Svelte 5 compatibility.

---

## Current Vue Configuration Analysis

### Chatwoot Vue Histoire Setup

```typescript
// histoire.config.ts
import { defineConfig } from 'histoire';
import { HstVue } from '@histoire/plugin-vue';

export default defineConfig({
  setupFile: './histoire.setup.ts',
  plugins: [HstVue()],
  collectMaxThreads: 4,
  vite: {
    server: { port: 6179 },
  },
  viteIgnorePlugins: ['vite-plugin-ruby'],
  theme: {
    darkClass: 'dark',
    title: '@chatwoot/design',
    logo: { /* ... */ },
  },
  defaultStoryProps: { /* ... */ },
  tree: { /* ... */ },
});
```

### Vue Story Format (Current)

```vue
<script setup>
import Button from './Button.vue';
const VARIANTS = ['solid', 'outline', 'faded', 'link', 'ghost'];
</script>

<template>
  <Story title="Components/Button">
    <Variant title="Basic Variants">
      <Button v-for="variant in VARIANTS" :variant="variant" />
    </Variant>
  </Story>
</template>
```

---

## Equivalent Svelte 5 SvelteKit Configuration

### Required Package Changes

**Remove Vue packages:**
```diff
- "@histoire/plugin-vue": "0.17.15"
- "@vitejs/plugin-vue": "^5.1.4"
- "vue": "^3.5.12"
- (all @vue/* packages)
```

**Add Svelte packages:**
```json
{
  "devDependencies": {
    "@histoire/plugin-svelte": "^0.17.15",
    "@sveltejs/kit": "^2.9.0",
    "@sveltejs/adapter-static": "^3.0.0",
    "svelte": "^5.7.0",
    "svelte-check": "^4.1.1"
  }
}
```

### Svelte Histoire Configuration

```typescript
// vite.config.ts (Svelte uses Vite config instead of histoire.config.ts)
import { HstSvelte } from '@histoire/plugin-svelte';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [sveltekit()],
  histoire: {
    plugins: [HstSvelte()],
    setupFile: './src/histoire.setup.ts',
    theme: {
      darkClass: 'dark',
      title: '@chatwoot/design',
      logo: { /* ... */ },
    },
    tree: {
      groups: [
        { id: 'top', title: '' },
        { id: 'components', title: 'Components' },
      ],
    },
  },
});
```

### Svelte Story Format (Target)

```svelte
<script>
  import Button from './Button.svelte';
  
  export let Hst;
  
  const VARIANTS = ['solid', 'outline', 'faded', 'link', 'ghost'];
</script>

<Hst.Story title="Components/Button">
  <Hst.Variant title="Basic Variants">
    <div class="flex flex-wrap gap-2 p-4 bg-n-background">
      {#each VARIANTS as variant}
        <Button {variant} label={variant} />
      {/each}
    </div>
  </Hst.Variant>
</Hst.Story>
```

---

## Conversion Challenges

### 1. Component Migration Effort

| Metric | Value | Impact |
|--------|-------|--------|
| Story files | 90 | Each needs manual conversion |
| Components | ~100+ | Full rewrite in Svelte syntax |
| Total Vue files | 500+ | Massive migration effort |

### 2. Framework-Specific Patterns

| Vue Pattern | Svelte 5 Equivalent | Effort |
|------------|-------------------|--------|
| `<script setup>` | `<script>` + `$props()` runes | Medium |
| `v-for` | `{#each}` | Low |
| `v-if/v-else` | `{#if}{:else}` | Low |
| `v-model` | `bind:value` | Low |
| `ref()` / `reactive()` | `$state()` rune | Medium |
| `computed()` | `$derived()` rune | Medium |
| `watch()` | `$effect()` rune | Medium |
| `<template>` | Direct HTML | Low |
| Props with types | `$props<T>()` | Low |
| Vuex/Pinia stores | Svelte stores / runes | High |
| Vue Router | SvelteKit routing | High |
| Composition API | Svelte 5 runes | Medium |
| Vue i18n | svelte-i18n | Medium |

### 3. Third-Party Library Compatibility

Many Vue-specific libraries need Svelte alternatives:

| Vue Library | Svelte Alternative | Status |
|-------------|-------------------|--------|
| `vue-router` | SvelteKit routing | ✅ Built-in |
| `pinia/vuex` | Svelte stores | ✅ Built-in |
| `vuelidate` | `superforms` / `felte` | ⚠️ Different API |
| `vue-multiselect` | Custom / `svelte-select` | ⚠️ Replace |
| `vue-virtual-scroller` | `svelte-virtual-list` | ⚠️ Replace |
| `floating-vue` | `@floating-ui/dom` | ✅ Direct use |
| `vue-i18n` | `svelte-i18n` | ⚠️ Different API |
| `@tanstack/vue-table` | `@tanstack/svelte-table` | ✅ Available |

### 4. SvelteKit SPA Mode Considerations

For SPA mode, configure adapter-static:

```javascript
// svelte.config.js
import adapter from '@sveltejs/adapter-static';

export default {
  kit: {
    adapter: adapter({
      fallback: 'index.html' // Enable SPA mode
    }),
    prerender: {
      entries: [] // No prerendering for pure SPA
    }
  }
};
```

---

## What Converts Easily

### ✅ Low Effort Items

1. **Histoire core configuration** - Similar structure
2. **Tailwind CSS** - Framework agnostic
3. **Design tokens** - CSS custom properties work universally
4. **Icon system** - Iconify works with Svelte
5. **Story structure** - Similar `<Story>` / `<Variant>` pattern

### ✅ Direct Mapping

```svelte
<!-- Vue -->
<Button v-for="variant in VARIANTS" :variant="variant" />

<!-- Svelte -->
{#each VARIANTS as variant}
  <Button {variant} />
{/each}
```

---

## What Requires Significant Work

### ❌ High Effort Items

1. **All 500+ Vue components** - Complete rewrite
2. **Vuex/Pinia stores** - Migrate to Svelte stores/runes
3. **Vue Router integration** - Migrate to SvelteKit routing
4. **Vue-specific plugins** - Find Svelte alternatives
5. **90 story files** - Manual conversion each

---

## Estimated Migration Effort

| Phase | Tasks | Estimate |
|-------|-------|----------|
| Setup | SvelteKit + Histoire config | 1-2 days |
| Core components | Button, Input, Avatar, etc. | 2-3 weeks |
| Complex components | Message bubbles, Forms | 3-4 weeks |
| Feature components | Contacts, Help Center, etc. | 4-6 weeks |
| Story files | 90 story files | 1-2 weeks |
| Testing & polish | Bug fixes, edge cases | 2-3 weeks |
| **Total** | | **3-4 months** |

---

## Recommendations

### Option 1: Keep Vue (Recommended)

If not migrating the main Chatwoot app:
- Histoire Vue setup works well
- No migration effort
- Ecosystem already established

### Option 2: Parallel Svelte Stories

If evaluating Svelte:
- Create a separate `/svelte-stories` directory
- Build a few key components as POC
- Keep Vue stories as primary

### Option 3: Full Svelte Migration

If migrating entire frontend:
- Plan for 3-4 month timeline
- Migrate core components first
- Stories can be converted alongside components

---

## Quick Start: Testing Svelte Stories

To test Svelte Histoire alongside Vue:

1. Create a separate Svelte project directory
2. Install required packages:
   ```bash
   pnpm create svelte@latest svelte-stories
   cd svelte-stories
   pnpm add -D histoire @histoire/plugin-svelte
   ```

3. Copy Tailwind config and design tokens
4. Create a test component and story
5. Run `pnpm story:dev`

---

## References

- [Histoire Svelte Plugin](https://histoire.dev/guide/svelte3/getting-started.html)
- [SvelteKit Example](https://github.com/histoire-dev/histoire/tree/main/examples/sveltekit)
- [Svelte 5 Runes](https://svelte.dev/docs/svelte/what-are-runes)
- [SvelteKit Adapter Static](https://kit.svelte.dev/docs/adapter-static)

---

**Document Version:** 1.0.0  
**Created:** December 2024
