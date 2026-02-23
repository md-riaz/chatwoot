## Conversations + Mobile Menu Parity Plan (Vue -> Svelte)

### Summary
Current Svelte parity gaps are concentrated in 3 areas:

1. Conversation navigation parity is broken:
- `AppSidebar` links to `/conversations/mentions`, `/unattended`, `/inbox/:id`, `/team/:id`, `/label/:label`, but these routes are missing, so links can 404.
2. Conversation list/filter behavior diverges from Vue:
- `mine`/`unassigned` tabs are not wired through to API/store behavior.
- Filter counts are mock-derived and use hardcoded assumptions.
- `custom_view/[id]` uses `window.innerWidth` inside reactive state (SSR-unsafe).
3. Responsive sidebar toggle parity is incomplete:
- Mobile sidebar does not reliably close after navigation.
- Floating launcher offset does not match mobile sidebar width.
- Launcher visibility logic only excludes numeric conversation detail routes.

### Public API / Interface Changes
1. Backend conversations list query contract (`GET /api/v1/accounts/:id/conversations`):
- Add support for `assignee_type` (`me`, `unassigned`, `all`)
- Add support for `unattended` (`true|false`)
- Add support for `mentioned` (`true|false`, scoped to current user)
- Add support for `label` (label title)
2. Frontend route surface:
- Add missing Svelte routes:
  - `/app/accounts/[accountId]/conversations/mentions`
  - `/app/accounts/[accountId]/conversations/unattended`
  - `/app/accounts/[accountId]/conversations/inbox/[id]`
  - `/app/accounts/[accountId]/conversations/team/[id]`
  - `/app/accounts/[accountId]/conversations/label/[label]`
3. Frontend data normalization:
- Normalize numeric backend `status`/`priority` to existing string enums in `transformConversationFromApi`.
- Normalize list time source to `lastActivityAt` (not `timestamp`).

### Implementation Plan

1. Fix backend filter parity first (to make route parity meaningful).
- Update `laravel/app/Http/Controllers/Api/V1/ConversationsController.php` `index()` filter whitelist to accept new params.
- Update `laravel/app/Repositories/Conversation/ConversationRepository.php` `findForAccount()`:
  - `assignee_type=me` -> `assignee_id = auth user`
  - `assignee_type=unassigned` -> `whereNull('assignee_id')`
  - `unattended=true` -> apply `Conversation::scopeUnattended()`
  - `mentioned=true` -> `whereHas('mentions', user_id=current_user_id)`
  - `label` -> `whereHas('labels', title=...)`
- Add `mentions()` relation on `laravel/app/Models/Conversation.php`.
- Keep existing status/inbox/team/priority semantics intact.

2. Add missing conversation route variants with Vue-style URLs.
- Create route files under `svelte-ui/src/routes/app/accounts/[accountId]/conversations/...` for `mentions`, `unattended`, `inbox/[id]`, `team/[id]`, `label/[label]`.
- Each route reuses existing conversations layout and applies a route preset into the conversations store before fetch.
- Use a shared route-preset helper (new small module) to avoid duplicated route->filter mapping logic.

3. Repair conversations store/filter wiring.
- In `svelte-ui/src/lib/stores/conversations.svelte.ts`, add explicit state for `assigneeType`, `unattended`, `mentioned`, `label`.
- Make `fetchConversations()` pass these params to API client.
- Ensure route preset + manual filters merge predictably with reset behavior.
- Add a `fetchMetaCounts()` call path so counts are API-backed instead of mock logic.

4. Repair conversations UI component parity.
- In `svelte-ui/src/lib/components/conversations/ConversationList.svelte`:
  - Wire `assigneeType` and route presets into store before fetch.
  - Remove hardcoded “mine id=1” counting logic.
- In `svelte-ui/src/lib/components/conversations/ConversationFilters.svelte`:
  - Initialize tab selection from store/route preset, not static `'all'`.
  - Keep clear/reset aligned with Vue expected default for each route.
- In `svelte-ui/src/lib/components/conversations/ConversationItem.svelte`:
  - Use `lastActivityAt` for time display.
- In `svelte-ui/src/routes/app/accounts/[accountId]/conversations/custom_view/[id]/+page.svelte`:
  - Remove direct `window.innerWidth` usage; use `is-mobile` hook / derived store-safe approach.

5. Fix responsive menu toggle parity.
- In `svelte-ui/src/lib/components/layout/SidebarMenuItem.svelte` and related sidebar link components:
  - On mobile navigation, explicitly close sidebar (`setOpenMobile(false)`).
- In `svelte-ui/src/lib/components/layout/MobileSidebarLauncher.svelte`:
  - Update visibility rule to account for all detail-style routes (including custom-view selected detail behavior).
  - Align launcher offset with mobile sidebar width constant.
- In sidebar state handling, add route-change close behavior for mobile sidebar to prevent stale open sheet state after navigation.

### Test Cases and Scenarios

1. Backend feature tests (`laravel/tests/Feature/Api/...`):
- `GET conversations?assignee_type=me`
- `GET conversations?assignee_type=unassigned`
- `GET conversations?unattended=true`
- `GET conversations?mentioned=true`
- `GET conversations?label=<title>`
- Combined filter interactions with status/inbox/team.

2. Frontend route smoke tests:
- Clicking each conversation sidebar item resolves to non-404 route and renders list/detail shell.

3. Frontend behavior tests:
- `mine`, `unassigned`, `open`, `resolved` tabs issue correct API params.
- Counts reflect backend meta data, not mocked computation.
- Conversation item time uses `lastActivityAt`.

4. Responsive tests (mobile viewport):
- Launcher opens sidebar.
- Selecting any sidebar link closes sidebar and navigates.
- Launcher visibility/position is correct on detail routes and when sheet is open.

5. SSR safety test:
- `custom_view/[id]` renders without `window` access errors.

### Assumptions and Defaults
1. Chosen default: keep Vue-style conversation path structure.
2. Chosen default: enforce exact Vue semantics for `Mentions` and `Unattended`, not placeholder mappings.
3. Source of Vue parity in this workspace is route/component parity docs plus existing route naming; raw Vue files are not present locally.
4. If advanced filter service gaps block exact semantics, backend query support will be added directly in `findForAccount()` to keep parity work unblocked.
