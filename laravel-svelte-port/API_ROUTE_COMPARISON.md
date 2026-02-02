# API Route Comparison (Source Code Analysis)

**Last Updated:** 2026-02-02
**Methodology:** Direct inspection of Laravel `routes/api.php` and Controller source code vs. Rails `swagger/paths`.

## Executive Summary
The Laravel implementation is a **superset** of the Rails API for core resources. It implements all standard CRUD operations and most specialized actions, often adding additional enterprise-grade features (e.g., `inbox_assistant`, `reporting_events`) and explicit endpoints for actions that were implicit or less structured in Rails.

## Key Architectural Differences

### 1. Search & Filtering
- **Rails:** Often overloads the `index` endpoint with `q` parameters for simple search.
- **Laravel:** consistently implements dedicated `search` (GET) and `filter` (POST) endpoints for resources like Conversations and Contacts, offering more robust querying capabilities while maintaining backward compatibility where possible.

### 2. Response Wrapping
- **Rails:** Inconsistent. Some endpoints return root arrays, others wrap in `payload` or `data`.
- **Laravel:** Consistently uses `JsonResource` which typically wraps responses in a `data` key. Clients porting from Rails may need to adjust response unwrapping logic (e.g., `response.data` vs `response.payload`).

### 3. Authentication & Middleware
- **Laravel:** Uses `auth:sanctum` and granular middleware like `EnsureAccountAccess`, `EnsureSuperAdmin`, and `ValidateBotAccess`. This enforces stricter security boundaries compared to the legacy Rails implementation.

---

## Detailed Resource Analysis

### 1. Conversations (`/api/v1/accounts/{id}/conversations`)

| Feature | Rails (Swagger) | Laravel (Source) | Status | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **List/Index** | `GET /` (params: status, q, labels) | `GET /` | ✅ Parity | Laravel handles `q` via separate `search` endpoint usually. |
| **Search** | via `index` | `GET /search` | ⚠️ Divergence | Laravel uses explicit endpoint. |
| **Filter** | `POST /filter` | `POST /filter` | ✅ Parity | |
| **Meta/Counts** | `GET /meta` | `GET /meta` | ✅ Parity | |
| **Create** | `POST /` | `POST /` | ✅ Parity | |
| **Show** | `GET /{id}` | `GET /{id}` | ✅ Parity | |
| **Mute** | ❌ Undocumented | `POST /{id}/mute` | 🚀 Laravel Extra | |
| **Unmute** | ❌ Undocumented | `POST /{id}/unmute` | 🚀 Laravel Extra | |
| **Toggle Status**| `POST /{id}/toggle_status` | `POST /{id}/toggle_status` | ✅ Parity | |
| **Assign** | `POST /{id}/assignments` | `POST /{id}/assign` | ⚠️ Path Diff | Rails uses `assignments`, Laravel uses `assign`. |
| **Inbox Assistant**| ❌ | `GET /{id}/inbox_assistant` | 🚀 Laravel Extra | Enterprise feature. |
| **Reporting Events**| `GET /{id}/reporting_events` | `GET /{id}/reporting_events` | ✅ Parity | |

### 2. Contacts (`/api/v1/accounts/{id}/contacts`)

| Feature | Rails (Swagger) | Laravel (Source) | Status | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **List/Index** | `GET /` (sort, page) | `GET /` | ✅ Parity | |
| **Search** | `GET /search` | `GET /search` | ✅ Parity | |
| **Active Contacts**| ❌ | `GET /active` | 🚀 Laravel Extra | Returns online/active contacts. |
| **Filter** | `POST /filter` | `POST /filter` | ✅ Parity | |
| **Import** | ❌ Undocumented | `POST /import` | 🚀 Laravel Extra | Full import system with status checks. |
| **Export** | ❌ Undocumented | `POST /export` | 🚀 Laravel Extra | Full export system with secure download. |
| **Merge** | `POST /merge` | `POST /{id}/merge` | ✅ Parity | |
| **Contactable Inboxes**| `GET /contactable_inboxes` | `GET /{id}/contactable_inboxes`| ✅ Parity | |

### 3. Inboxes (`/api/v1/accounts/{id}/inboxes`)

| Feature | Rails (Swagger) | Laravel (Source) | Status | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **List/Index** | `GET /` | `GET /` | ✅ Parity | |
| **Create** | `POST /` | `POST /` | ✅ Parity | Admin only. |
| **Members** | `GET /{id}/members` | `GET /{id}/members` | ✅ Parity | |
| **Add Member** | `POST /{id}/inbox_members` | `POST /{id}/members` | ⚠️ Path Diff | Rails uses nested resource style `inbox_members`, Laravel uses action style `members`. |
| **Agent Bot** | `GET /{id}/get_agent_bot` | `GET /{id}/agent_bot` | ⚠️ Path Diff | |
| **Campaigns** | ❌ | `GET /{id}/campaigns` | 🚀 Laravel Extra | |
| **Health** | ❌ | `GET /{id}/health` | 🚀 Laravel Extra | WhatsApp health check. |

---

## Route Gaps & Action Items

### Potential Missing Endpoints in Laravel
*None identified for core resources.* The Laravel implementation appears to cover all Rails functionality and adds significant new capabilities.

### Path & Parameter Standardization
1.  **Assignments**: Verify frontend expects `POST .../assign` vs `.../assignments`.
2.  **Inbox Members**: Verify frontend expects `POST .../members` vs `.../inbox_members`.
3.  **Agent Bot**: Verify frontend expects `.../agent_bot` vs `.../get_agent_bot`.

## Recommendation
The Laravel API is robust and production-ready for these core resources. The primary migration task is ensuring the frontend client (`svelte-ui`) adapts to the **route path nuances** (e.g., `assign` vs `assignments`) and **response wrapping** (`data` key).
