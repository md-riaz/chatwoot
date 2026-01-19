# Sidebar Implementation Guide — Vue → SvelteKit

This document explains how the `/app/` sidebar is constructed in the Vue frontend and gives concrete, actionable guidance and tech insights for re-implementing equivalent behavior in SvelteKit.

## Overview (how Vue builds the sidebar)

- Shell: `Dashboard.vue` mounts the sidebar component (`NextSidebar` / `dashboard/components-next/sidebar/Sidebar.vue`) and a `router-view` for route content. Global widgets and modals are siblings of the router view.
- Composition: `Sidebar.vue` composes smaller components: `SidebarAccountSwitcher.vue`, `SidebarGroup.vue`, `ChannelLeaf.vue`, `SidebarProfileMenu.vue`, `SidebarChangelogCard.vue`, `ComposeConversation.vue`, and `MobileSidebarLauncher.vue`.
- Data-driven menu: `Sidebar.vue` constructs a `menuItems` computed array that contains the full menu structure. Each item may include: `name`, `label`, `icon`, `to` (route), `activeOn`, `children`, `getterKeys` (for counts), and `renderComponent` for custom leaf rendering.
- Dynamic children: Inbox, labels, teams and custom views are populated by mapping Vuex store getters (e.g., `inboxes`, `labels`, `teams`, `customViews`).
- Lifecycle: `onMounted` dispatches store actions to fetch `labels`, `inboxes`, `notifications/unReadCount`, `teams`, and `customViews` so the sidebar shows live data.
- Routing helpers: `frontendURL` / `accountScopedRoute` build route paths; route `name` values (e.g., `inbox_view`, `home`) and `activeOn` arrays are used to compute active states.
- Permissions & flags: menu visibility is gated via `meta.permissions` and `featureFlag` checks; `globalConfig` getters are used to hide/show features.
- UX & accessibility: keyboard shortcuts via `useKbd` / `useSidebarKeyboardShortcuts`, dropdown primitives (`DropdownContainer`, `DropdownBody`, `DropdownItem`), and click-outside (`v-on-click-outside`) are used.

## Helpers, composables & files to inspect (Vue)

- `dashboard/components-next/sidebar/Sidebar.vue` — main menu builder and view.
- `dashboard/components-next/sidebar/SidebarAccountSwitcher.vue` — account dropdown behavior.
- `dashboard/composables/useAccount.js` — account helpers: `accountId`, `currentAccount`, `accountScopedRoute`, cloud/feature helpers.
- `dashboard/composables/store.js` / `useMapGetter()` — Vuex access wrapper used to read getters.
- `dashboard/helper/URLHelper.js` — `frontendURL` and route builders.
- `dashboard/components-next/sidebar/SidebarGroup.vue`, `ChannelLeaf.vue`, `SidebarProfileMenu.vue` — rendering primitives.
- `next/dropdown-menu/base` — dropdown primitives used by the account switcher.

## SvelteKit one-to-one mapping (high-level)

- Vue `Dashboard.vue` → SvelteKit `src/routes/app/+layout.svelte` (layout-level shell).
- `Sidebar.vue` → `src/lib/components/Sidebar.svelte`.
- `SidebarGroup.vue` → `src/lib/components/SidebarGroup.svelte` (recursive group renderer).
- `SidebarAccountSwitcher.vue` → `src/lib/components/SidebarAccountSwitcher.svelte` (dropdown component).
- Vuex modules → Svelte stores (`src/lib/stores/*`): `account`, `inboxes`, `labels`, `teams`, `customViews`, `notifications`, `globalConfig`.
- `frontendURL` / `accountScopedRoute` → `src/lib/utils/route.js` providing a consistent `accountScopedRoute(name, params)` string builder; use SvelteKit `goto(path)` for navigation.
- Dropdown primitives → `shadcn-svelte` components/primitives (Tailwind v4). `shadcn-svelte` commonly composes primitives that use `data-slot` attributes and child composition rather than relying on named `<slot>` hacks. Prefer the library's primitives and composition APIs (bindings, `bind:ref`, `data-slot` usage) for dropdowns/menus to ensure accessibility and consistent styling in Svelte 5.

## Concrete SvelteKit implementation guidance and patterns

1. Layout and placement

  - `src/routes/app/+layout.svelte` will import `Sidebar.svelte` and render the main page outlet (the layout's default content area). Place global widgets and modals at layout level so they're shared across child pages.

2. Stores (replace Vuex)

  - Create stores per domain: `account.js`, `inboxes.js`, `labels.js`, `teams.js`, `customViews.js`, `notifications.js`, `uiSettings.js`.
  - Each store exports a `load()` or `fetch()` method that calls the API and updates the writable store. Use `derived()` for computed lists (e.g., `sortedInboxes`).
  - Example API surface:

  - account.js:

    - `export const currentAccount = writable(null)`
    - `export const accountId = derived(currentAccount, a => a?.id)`
    - `export async function loadAccount()` — fetch user/account and set stores.

3. Menu data model

  - Build the menu as a JS data structure in `Sidebar.svelte` or a helper `menuFactory.js`:

    - item: { name, labelKey, icon, to, activeOn, children?, countStore? , renderComponent? }
    - For children from stores, compute the array reactively: `$: inboxChildren = $inboxes.map(...)`.

  - Keep menu-building logic pure where possible and testable — export a function `buildMenu({inboxes, labels, teams, customViews, uiSettings})` that returns `menuItems`.

4. Rendering and custom leafs

  - For inline custom rendering (Vue `h(ChannelLeaf, {...})`), use `<svelte:component this={item.renderComponent} {...props} />` in Svelte.
  - Sidebar groups: `SidebarGroup.svelte` renders title, toggles open/close, and iterates children (recursively if nested).

5. Account switcher & dropdowns

  - Implement a dropdown using `shadcn-svelte` primitives (use the library's trigger/content primitives or `data-slot` composition patterns). Use a Svelte action `use:clickOutside` to close the list.
  - `SidebarAccountSwitcher.svelte` will read `userAccounts` store and call `goto(accountPath)` on selection.

6. Keyboard shortcuts & accessibility

  - Use a small global hotkey helper (`hotkeys-js` or `Mousetrap`) registered in the layout `onMount` or as a Svelte action. Provide `aria-*` attributes and role semantics for lists and menu items.

7. Routing & active detection

  - Use `$page.url.pathname` to compute active state. Port `activeOn` semantics by checking route name equivalents or path patterns. Provide a helper `isActive(item, $page)` used by `SidebarGroup`.

8. Permissions & feature flags

  - Expose `currentUser` and `globalConfig` stores and helper `hasPermission(permission)` and `isFeatureEnabled(flag)` to decide which menu items to show.

9. Data fetching

  - In `+layout.server.js` (SSR), load minimal account/user data. On client `onMount` call `inboxes.load()`, `labels.load()`, `teams.load()`, `customViews.load()`, and `notifications.loadUnread()` to match Vue `onMounted` behavior.

10. Event bus / cross-component events

  - Replace Vue `emitter` event bus with either:
    - Svelte stores (writable) for global booleans (e.g., `showComposer`), or
    - A tiny event emitter util exported from `src/lib/utils/eventBus.js`.

## File layout suggestions (SvelteKit)

- src/routes/app/+layout.svelte — dashboard shell (Sidebar + page outlet + global widgets)
- src/lib/components/Sidebar.svelte
- src/lib/components/SidebarGroup.svelte
- src/lib/components/SidebarAccountSwitcher.svelte
- src/lib/components/ChannelLeaf.svelte
- src/lib/components/SidebarProfileMenu.svelte
- src/lib/components/ComposeConversation.svelte
- src/lib/stores/account.js, inboxes.js, labels.js, teams.js, customViews.js, notifications.js, uiSettings.js
- src/lib/utils/route.js — `accountScopedRoute` and helpers
- src/lib/actions/clickOutside.js — Svelte action
- src/lib/actions/keyboardShortcuts.js — Svelte action or helper
- src/lib/i18n/index.js — svelte-i18n setup (to replace vue-i18n)

## Implementation pitfalls & tech insights

- Keep `menuItems` data-driven — this allows using the same data to render mobile, desktop, and accessible views.
- Recreate `accountScopedRoute` semantics to keep deep-link parity with existing backend URLs.
- Vue `h()` render-fn usage becomes `<svelte:component />` in Svelte — pass concrete component references rather than inline render functions.
- Vuex getters → derived Svelte stores: preserve names and shapes where possible to reduce friction when porting other logic.
- For SSR, fetch critical user/account info server-side to enable permission gating and avoid client-only redirects.
- Test the pure menu builder function independently (unit test) to ensure feature flags and permissions are applied consistently.

## Next actions (suggested)

- Export the Vue `menuItems` factory into a JSON/JS spec to bootstrap Svelte menu data.
- Scaffold the suggested Svelte files and minimal stores so the layout renders and navigation works.

---
Created for Svelte migration: guidance to reimplement `/app/` sidebar and related helpers in SvelteKit.
