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

## Concrete SvelteKit implementation guidance and patterns (IMPLEMENTED)

1. Layout and placement (Completed)

  - `src/routes/app/+layout.svelte` acts as the main shell. It wraps the content in `<Sidebar.Provider>` and `<Sidebar.Inset>`.
  - Global keyboard shortcuts (`Cmd+K`, `Cmd+/`, `Alt+O`) are handled via `window` event listeners in the layout.
  - Reverb WebSocket client is initialized here to subscribe to `user.{id}` channels for real-time notifications.

2. Stores (Replaced Vuex with Svelte 5 Runes)

  - Implemented domain-specific stores using `.svelte.ts` files: `auth.svelte.ts`, `inboxes.svelte.ts`, `labels.svelte.ts`, `teams.svelte.ts`, `customViews.svelte.ts`, `notifications.svelte.ts`.
  - Stores use `$state` for reactivity and expose `fetch()` methods.
  - `notificationsStore` handles optimistic updates via WebSocket events (`handleNewNotification`).

3. Menu data model (Completed)

  - `src/lib/components/layout/AppSidebar.svelte` builds the menu structure dynamically.
  - Menu items are derived from store data using `$derived` runes to ensure reactivity.
  - Structure follows the Vue pattern: `primary` (Inboxes, Teams), `secondary` (Reports, Settings), and `custom` (Labels, Custom Views).

4. Rendering and custom leafs (Completed)

  - `src/lib/components/layout/SidebarGroup.svelte` handles recursive rendering of menu groups.
  - Supports icon rendering for sub-items (e.g., specific Inbox channel icons) using dynamic component references.
  - Active state is calculated using `$page.url.pathname` and helper functions.

5. Account switcher & dropdowns (Completed)

  - `SidebarHeader` contains the account switcher, using `shadcn-svelte`'s `DropdownMenu` primitives.
  - Account switching is handled by `authStore` and navigation.

6. Keyboard shortcuts & accessibility (Completed)

  - Global shortcuts:
    - `Cmd+K` / `Ctrl+K`: Navigate to Search.
    - `Cmd+/` / `Ctrl+/`: Open Keyboard Shortcuts modal.
    - `Alt+O`: Toggle Sidebar (Legacy support).
  - Sidebar toggle via `Cmd+B` is handled by `Sidebar.Provider`.
  - `KeyboardShortcutsModal.svelte` displays available shortcuts dynamically.

7. Routing & active detection (Completed)

  - `isNavigationItemActive` helper function compares the current URL with menu item paths.
  - Supports exact matches and prefix matches for nested routes.

8. Permissions & feature flags (Completed)

  - Menu items are conditionally rendered based on `authStore.currentUser` permissions and role checks (e.g., admin-only settings).

9. Data fetching (Completed)

  - `+layout.svelte` triggers parallel fetching of initial data (`inboxes`, `labels`, `teams`, `notifications`) on mount.
  - WebSocket connection ensures data stays fresh without manual polling.

10. Event bus / cross-component events (Completed)

  - Replaced Vue event bus with native DOM events or Svelte stores where appropriate.
  - `open-keyboard-shortcuts` custom event is used to trigger the modal from anywhere.

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
