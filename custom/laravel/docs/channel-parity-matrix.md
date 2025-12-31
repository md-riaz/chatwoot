# Channel Parity Matrix (Laravel Port)

This matrix captures the concrete inbound/outbound paths, auth/verification, attachments, and error/idempotency handling per channel.

## Email
- **Inbound**: `POST /api/v1/webhooks/email` → `Channels\EmailController@inbound` → `ProcessInboundEmailJob` → `InboundMessageService` (creates Contact/Conversation/Message). 【F:routes/api.php†L62-L73】【F:app/Jobs/Channels/ProcessInboundEmailJob.php†L14-L174】
- **Outbound**: `SendReplyJob` → `Services/Channels/Email` (via channel morph) when message inbox channel is `Channels\Email`. 【F:app/Jobs/SendReplyJob.php†L17-L90】
- **Auth/Signature**: None (provider-specific inbound endpoints expected to carry their own trust). Provider credentials stored on `channel_email` (`imap_*`, `smtp_*`, `provider_config`). 【F:app/Http/Controllers/Api/V1/Channels/EmailController.php†L21-L105】
- **Verification**: None; provider tokens validated upstream.
- **Attachments**: Downloaded via HTTP/base64/temporary path, stored to `public` disk, saved as `Attachment` linked to message. 【F:app/Jobs/Channels/ProcessInboundEmailJob.php†L92-L172】
- **Errors/Idempotency**: External message ID checked (`external_source_id`). Warnings/errors logged; Redis counter for attachment metrics. Job retries via queue defaults.

## API (integration/public API)
- **Inbound**: Authenticated API controllers create messages via Actions (e.g., `MessagesController`, `Public\MessagesController`). 【F:routes/api.php†L120-L153】
- **Outbound**: `SendReplyJob` dispatches channel service based on inbox channel. 【F:app/Jobs/SendReplyJob.php†L31-L90】
- **Auth/Signature**: `auth:sanctum` + account middleware. No webhook verification.
- **Attachments**: Standard upload flow via `AttachmentsController`/`WidgetDirectUploadsController`.
- **Errors/Idempotency**: Handled by Actions/Repositories; no webhook retries needed.

## Web Widget
- **Inbound**: Widget routes under `/api/v1/widget` controllers using Actions. 【F:routes/api.php†L92-L118】
- **Outbound**: Same as API; realtime via Reverb channels.
- **Auth/Signature**: Widget token validation within widget controllers.
- **Attachments**: Widget direct uploads, stored via existing attachment pipeline.
- **Errors/Idempotency**: Domain actions + repositories enforce consistency.

## WhatsApp
- **Inbound**: `GET/POST /api/v1/webhooks/whatsapp` → verify token match to `channel_whatsapp.provider_config.verify_token`, process via `ProcessWhatsAppWebhookJob` → `InboundMessageService`. 【F:routes/api.php†L68-L75】【F:app/Http/Controllers/Api/V1/Channels/WhatsAppController.php†L21-L103】【F:app/Jobs/Channels/ProcessWhatsAppWebhookJob.php†L15-L96】
- **Outbound**: `SendOnWhatsappService` invoked by `SendReplyJob`. Supports Facebook Cloud/Twilio/other providers. 【F:app/Services/Channels/Whatsapp/SendOnWhatsappService.php†L1-L135】【F:app/Jobs/SendReplyJob.php†L31-L90】
- **Auth/Signature**: Verify token on GET challenge; provider access tokens stored on channel provider_config.
- **Verification**: Token challenge response in controller.
- **Attachments**: Not yet downloaded; webhook metadata persisted; inbound text normalized.
- **Errors/Idempotency**: `external_source_id` checked before ingest; job logging on failures.

## SMS / Twilio
- **Inbound**: `POST /api/v1/webhooks/sms` → Twilio signature validation (using channel auth_token) → `ProcessSmsWebhookJob` → `InboundMessageService`. 【F:app/Http/Controllers/Api/V1/Channels/SmsController.php†L17-L101】【F:app/Jobs/Channels/ProcessSmsWebhookJob.php†L15-L88】
- **Outbound**: `SendOnTwilioSmsService` via `SendReplyJob`. 【F:app/Services/Channels/TwilioSms/SendOnTwilioSmsService.php†L1-L150】
- **Auth/Signature**: `X-Twilio-Signature` validated against request body and channel auth token.
- **Verification**: Signature check; otherwise 403.
- **Attachments**: Twilio media URLs mapped to attachments in outbound; inbound attachments not yet downloaded (text normalized).
- **Errors/Idempotency**: `external_source_id` check prevents duplicates; logging on failures; queue retries.

## Telegram
- **Inbound**: `POST /api/v1/webhooks/telegram/{inboxId}` → optional secret header validation → `ProcessTelegramWebhookJob` → `InboundMessageService`. 【F:routes/api.php†L74-L80】【F:app/Http/Controllers/Api/V1/Channels/TelegramController.php†L21-L86】【F:app/Jobs/Channels/ProcessTelegramWebhookJob.php†L1-L74】
- **Outbound**: `TelegramService` used by channel send service when invoked from `SendReplyJob` candidate list. 【F:app/Services/Channels/Telegram/TelegramService.php†L1-L209】【F:app/Jobs/SendReplyJob.php†L31-L90】
- **Auth/Signature**: `X-Telegram-Bot-Api-Secret-Token` compared to channel `webhook_secret`.
- **Verification**: None (Telegram setWebhook flow sets secret).
- **Attachments**: Message metadata stored; media download helper in `TelegramService`.
- **Errors/Idempotency**: `external_source_id` checked; warnings logged on invalid inbox/secret.

## Line
- **Inbound**: `POST /api/v1/webhooks/line` → signature validation per channel → ingested via `InboundMessageService`. 【F:routes/api.php†L76-L80】【F:app/Http/Controllers/Api/V1/Channels/LineController.php†L21-L125】
- **Outbound**: Not implemented (requires LINE push/reply client).
- **Auth/Signature**: `X-Line-Signature` HMAC with channel secret.
- **Verification**: Signature required; otherwise 403.
- **Attachments**: Text normalized; attachments pending.
- **Errors/Idempotency**: `external_source_id` checked; invalid signatures logged.

## Facebook
- **Inbound**: `GET/POST /api/v1/webhooks/facebook` (CRC verify + events) dispatches `ProcessFacebookWebhookJob`; webhook payload normalized via `InboundMessageService` (messages/postbacks) with attachment download. 【F:routes/api.php†L66-L72】【F:app/Jobs/Channels/ProcessFacebookWebhookJob.php†L1-L34】【F:app/Services/Channels/Facebook/FacebookService.php†L200-L284】
- **Outbound**: `SendOnFacebookService` via `SendReplyJob`. 【F:app/Services/Channels/Facebook/SendOnFacebookService.php†L1-L71】
- **Auth/Signature**: Verify token via controller; page access tokens stored on channel model.
- **Verification**: Graph webhook verification supported via GET.
- **Attachments**: Media URLs downloaded and stored via inbound service.
- **Errors/Idempotency**: `facebook_message_events` idempotency table plus `external_source_id` checks; queue retries and logging.

## Twitter/X
- **Inbound**: `GET /api/v1/webhooks/twitter` CRC check; `POST /api/v1/webhooks/twitter` → `ProcessTwitterWebhookJob` → `InboundMessageService` (DM events). 【F:routes/api.php†L70-L72】【F:app/Http/Controllers/Api/V1/Channels/TwitterController.php†L55-L63】【F:app/Jobs/Channels/ProcessTwitterWebhookJob.php†L1-L74】
- **Outbound**: `SendOnTwitterProfileService` via `SendReplyJob`. 【F:app/Services/Channels/TwitterProfile/SendOnTwitterProfileService.php†L1-L73】
- **Auth/Signature**: CRC response uses consumer secret (config).
- **Verification**: CRC implemented.
- **Attachments**: DM media URLs captured in metadata and saved as attachments via inbound service.
- **Errors/Idempotency**: `external_source_id` check on DM event id; invalid inbox logs warning.

## Instagram
- **Inbound**: `GET/POST /api/v1/webhooks/instagram` → `InstagramController@verifyWebhook|webhook` → `ProcessInstagramWebhookJob` → `InboundMessageService`. 【F:routes/api.php†L70-L73】【F:app/Http/Controllers/Api/V1/Channels/InstagramController.php†L1-L75】【F:app/Jobs/Channels/ProcessInstagramWebhookJob.php†L1-L72】
- **Outbound**: `SendOnInstagramService` candidate in `SendReplyJob` (for IG DMs). 【F:app/Services/Channels/Instagram/SendOnInstagramService.php†L1-L75】【F:app/Jobs/SendReplyJob.php†L59-L71】
- **Auth/Signature**: Verify token via `services.instagram.verify_token` on GET challenge.
- **Verification**: GET challenge implemented in controller; POST currently trust-based (add signature when provider docs finalized).
- **Attachments**: Text/caption normalized; raw payload stored in metadata; media URL extraction placeholder uses first attachment URL if present.
- **Errors/Idempotency**: `external_source_id` checked; missing inbox logs warning; queue retries handle transient errors.

## TikTok
- **Inbound**: `GET/POST /api/v1/webhooks/tiktok` → `TiktokController` → `ProcessTiktokWebhookJob` → `InboundMessageService`. 【F:routes/api.php†L72-L75】【F:app/Jobs/Channels/ProcessTiktokWebhookJob.php†L1-L45】
- **Outbound**: Not implemented.
- **Auth/Signature**: Challenge echo supported; signature validation not implemented.
- **Verification**: Challenge response on GET.
- **Attachments**: Not implemented (text/metadata stored).
- **Errors/Idempotency**: `external_source_id` check; warns if inbox missing.

## Voice (Twilio)
- **Inbound**: `POST /api/v1/webhooks/voice/call/{phone}` and status endpoints → `VoiceController` (Twilio webhook). 【F:routes/api.php†L77-L82】
- **Outbound**: `Services/Voice/TwilioService` used by call flows (SendReplyJob not applicable).
- **Auth/Signature**: Should leverage Twilio signature check (not yet implemented on controller).
- **Verification**: None currently.
- **Attachments**: N/A.
- **Errors/Idempotency**: Webhook logging and status updates handled in controller/jobs.
