# WebSocket Comparison: Rails ActionCable vs Laravel Reverb

This document compares the current Rails ActionCable implementation (source of truth) with the Laravel Reverb implementation in the migration project. It focuses on channel naming, authentication/authorization patterns, broadcast event names, payload shapes, and identifies gaps that need porting attention.

## Sources of Truth

### Rails (ActionCable)
- Channel subscription logic: `app/channels/room_channel.rb`
- Broadcast event fan-out: `app/listeners/action_cable_listener.rb`
- Broadcast job and payload normalization: `app/jobs/action_cable_broadcast_job.rb`
- Event type constants: `lib/events/types.rb`

### Laravel (Reverb)
- Channel authorization: `laravel-svelte-port/laravel/routes/channels.php`
- Broadcast events:
  - `laravel-svelte-port/laravel/app/Events/Conversation/*`
  - `laravel-svelte-port/laravel/app/Events/Message/*`
  - `laravel-svelte-port/laravel/app/Events/Contact/*`
  - `laravel-svelte-port/laravel/app/Events/Broadcasting/NotificationCreatedBroadcast.php`
  - `laravel-svelte-port/laravel/app/Events/Portal/PortalUpdated.php`
  - `laravel-svelte-port/laravel/app/Events/Article/ArticleUpdated.php`
  - `laravel-svelte-port/laravel/app/Events/Sla/SlaBreached.php`

## Channel Naming & Subscription Model

| Concern | Rails ActionCable | Laravel Reverb | Gap / Notes |
| --- | --- | --- | --- |
| Primary channel name | `pubsub_token` (per user/contact) + `account_{id}` | `account.{accountId}`, `conversation.{conversationId}`, `user.{userId}`, `account.{accountId}.presence`, `portal.{portalId}`, `article.{articleId}` | Rails uses token-based channel naming; Laravel uses ID-based private/presence channels. Alignment requires mapping tokens to IDs or updating client subscription strategy. |
| Subscription entry point | `RoomChannel` with `pubsub_token`, `user_id`, `account_id` params | Broadcast auth via `routes/channels.php` | Rails uses a single channel with param-driven subscriptions; Laravel uses per-channel auth callbacks. |
| Presence updates | `RoomChannel#broadcast_presence` emits `presence.update` to `pubsub_token` | Presence channel exists: `account.{accountId}.presence` | No Laravel event currently emits presence updates; presence payload parity needed. |

## Authentication & Authorization

| Concern | Rails ActionCable | Laravel Reverb | Gap / Notes |
| --- | --- | --- | --- |
| Auth token | `pubsub_token` for user/contact | Standard Laravel auth (Sanctum/Pusher-compatible) | Laravel uses authenticated user session; Rails uses pubsub tokens for contacts and agents. |
| Account scoping | `current_account` derived from user/contact | Channel auth checks account membership | Should ensure contact-based channels are supported (Rails supports contacts via `ContactInbox` lookup). |

## Broadcast Event Inventory (Rails → Laravel)

### Rails ActionCable Event Types (from `Events::Types`)

| Event Name | Triggered In Rails | Broadcast Target(s) | Laravel Reverb Equivalent | Gap |
| --- | --- | --- | --- | --- |
| `notification.created` | `ActionCableListener#notification_created` | User `pubsub_token` | `NotificationCreatedBroadcast` (`notification.created`) | **Partial**: Laravel uses `App.Models.User.{id}` private channel; ensure client listens and payload parity. |
| `notification.updated` | `ActionCableListener#notification_updated` | User `pubsub_token` | — | **Missing** |
| `notification.deleted` | `ActionCableListener#notification_deleted` | User `pubsub_token` | — | **Missing** |
| `account.cache_invalidated` | `ActionCableListener#account_cache_invalidated` | Account user tokens | — | **Missing** |
| `message.created` | `ActionCableListener#message_created` | Account users + contact inbox tokens | `MessageCreated` (`message.created`) on `account.{id}` + `conversation.{id}` | **Partial**: Rails includes contact tokens and filters private/activity messages; confirm Laravel contact inbox behavior. |
| `message.updated` | `ActionCableListener#message_updated` | Account users + contact inbox tokens | `MessageUpdated` (`message.updated`) | **Partial**: Rails includes `previous_changes`; Laravel event payload should be checked for parity. |
| `first.reply.created` | `ActionCableListener#first_reply_created` | Account users | — | **Missing** |
| `conversation.created` | `ActionCableListener#conversation_created` | Account users + contact inbox tokens | `ConversationCreated` (`conversation.created`) on `account.{id}` | **Partial**: Rails includes contact tokens. |
| `conversation.read` | `ActionCableListener#conversation_read` | Account users | — | **Missing** |
| `conversation.status_changed` | `ActionCableListener#conversation_status_changed` | Account users + contact inbox tokens | `ConversationStatusChanged` (`conversation.status_changed`) | **Partial**: contact token fan-out not visible in Laravel. |
| `conversation.updated` | `ActionCableListener#conversation_updated` | Account users + contact inbox tokens | `ConversationUpdated` (`conversation.updated`) | **Partial**: contact token fan-out not visible in Laravel. |
| `conversation.typing_on` | `ActionCableListener#conversation_typing_on` | Account users + contact inbox tokens (excluding origin) | — | **Missing** |
| `conversation.typing_off` | `ActionCableListener#conversation_typing_off` | Account users + contact inbox tokens (excluding origin) | — | **Missing** |
| `assignee.changed` | `ActionCableListener#assignee_changed` | Account users | `ConversationAssigned` (`conversation.assigned`) | **Different name**: event name and payload differ from Rails. |
| `team.changed` | `ActionCableListener#team_changed` | Account users | — | **Missing** |
| `conversation.contact_changed` | `ActionCableListener#conversation_contact_changed` | Account users | — | **Missing** |
| `contact.created` | `ActionCableListener#contact_created` | `account_{id}` | `ContactCreated` (`contact.created`) | **Likely parity** (payload check needed). |
| `contact.updated` | `ActionCableListener#contact_updated` | `account_{id}` | `ContactUpdated` (`contact.updated`) | **Likely parity** (payload check needed). |
| `contact.merged` | `ActionCableListener#contact_merged` | `account_{id}` | — | **Missing** |
| `contact.deleted` | `ActionCableListener#contact_deleted` | `account_{id}` | — | **Missing** |
| `conversation.mentioned` | `ActionCableListener#conversation_mentioned` | User `pubsub_token` | — | **Missing** |

### Laravel-Only Reverb Events (No Rails ActionCable Equivalent Documented)

| Event Name | Broadcast Channels | Notes |
| --- | --- | --- |
| `conversation.assigned` | `account.{id}`, `conversation.{id}`, `user.{id}` | Laravel-specific naming; not in Rails types list (closest Rails event: `assignee.changed`). |
| `message.deleted` | `account.{id}`, `conversation.{id}` | Rails ActionCable does not broadcast a message-deleted event. |
| `article.updated` | `article.{id}` | Reverb-only; Rails ActionCable does not document a matching event. |
| `portal.updated` | `portal.{id}` | Reverb-only; Rails ActionCable does not document a matching event. |
| `sla.breached` | `account.{id}` | Reverb-only; Rails ActionCable does not document a matching event. |

## Payload Shape Differences (Documented)

| Topic | Rails ActionCable | Laravel Reverb | Gap / Notes |
| --- | --- | --- | --- |
| Event envelope | `{ event: <event_name>, data: <payload> }` | Laravel `broadcastWith()` payload only (event name is separate) | Frontend adapter must align expectations (Rails includes `event` key). |
| Performer attribution | Rails injects `performer` via `ActionCableListener#broadcast` | Not present in Laravel events by default | Consider adding `performer` data to match Rails UI expectations. |
| Conversation update de-dupe | `ActionCableBroadcastJob` reloads latest conversation for some events | Laravel events use model state at dispatch time | Potential stale payload differences for conversation updates. |

## Known Gaps & Porting Checklist

1. **Presence updates**
   - Rails emits `presence.update` on `pubsub_token` channels.
   - Laravel defines a presence channel but does not emit an equivalent event.
2. **Notification updates/deletes**
   - Rails broadcasts `notification.updated` and `notification.deleted`.
   - Laravel has `notification.created` only.
3. **Conversation read/typing/contact change/team change/mentioned**
   - Rails broadcasts `conversation.read`, `conversation.typing_on/off`, `conversation.contact_changed`, `team.changed`, `conversation.mentioned`.
   - Laravel does not have documented events for these.
4. **Contact merge/delete**
   - Rails broadcasts `contact.merged` and `contact.deleted`.
   - Laravel does not have documented equivalents.
5. **Channel naming strategy**
   - Rails uses `pubsub_token` and `account_{id}` strings for channels.
   - Laravel uses `account.{id}`, `conversation.{id}`, `user.{id}`.
   - Requires frontend/client alignment and mapping for contacts (pubsub token support).
6. **Event naming parity**
   - Rails `assignee.changed` vs Laravel `conversation.assigned`.
   - Consider aliasing or mapping to ensure UI parity.

## Suggested Follow-ups

- Confirm frontend subscription implementation (`svelte-ui/src/lib/websocket`) supports Laravel channel names and handles Rails-style event envelope if still expected.
- Add Laravel broadcast events for missing Rails ActionCable events with parity payloads.
- Decide on token-based channel support for contact sessions if required for widget parity.

