# Project TODOs (custom/laravel migration)

Version: 1.0.0
Last Updated: 2025-12-30

Summary
=======
This document captures the current migration/implementation gaps that remain while porting the Rails backend into `custom/laravel`. It is a concise, runnable checklist intended for contributors to pick up work quickly.

How to use
----------
- Each entry has: ID, Title, Status, Short description, and Next steps.
- Status: not-started / in-progress / completed
- When you start work, update the status in the centralized todo tracker and add a short progress note.

Tasks
-----

1. Shopify integration: webhook job + OAuth
   - Status: completed
   - Description: Webhook job created (`app/Jobs/Integrations/ProcessShopifyWebhookJob.php`) and OAuth endpoints added in `ShopifyController`.
   - Next: Subscribe webhooks automatically and add topic-specific handlers (orders/customers).

2. Assignment Policies V2 (update)
   - Status: completed
   - Description: `AutoAssignConversationAction` updated with team preference, skills-based routing, capacity (per-inbox limits), round-robin and fair-distribution logic. DB eager-loading added.
   - Next: Add integration tests and metrics around assignment decisions.

3. Facebook webhooks
   - Status: completed
   - Description: `FacebookController::webhook` verifies signatures and dispatches `ProcessFacebookWebhookJob`. `FacebookService::processWebhook()` implements idempotent mapping to `Contact`/`Conversation`/`Message` and dispatches `ConversationCreated`/`MessageCreated` events.
   - Next: Add unit/integration tests and observability for mapping; monitor idempotency table growth.

3.1 Facebook page subscription
   - Status: completed
   - Description: When creating a Facebook inbox, the channel record is persisted to `channel_facebook_pages` and the app enqueues `SubscribeFacebookPageJob` to subscribe the app to the page. Backoff and `failed()` handler implemented to log subscription failures.
   - Files: `app/Http/Controllers/Api/V1/Channels/FacebookController.php`, `app/Models/Channels/FacebookPage.php`, `app/Services/Channels/Facebook/FacebookService.php`, `app/Jobs/Channels/SubscribeFacebookPageJob.php`.
   - Next: Surface failures to the UI and add metrics.

4. Facebook webhook event mapping
   - Status: completed
   - Description: `FacebookService::processWebhook()` converts parsed Facebook events into `Contact`, `Conversation`, and `Message` domain objects. Idempotency ensured via `facebook_message_events` table; events `ConversationCreated` and `MessageCreated` are emitted for downstream listeners.
   - Next: Add unit/integration tests and observability; validate event ordering under load.

5. WhatsApp/Twilio inbound processing
   - Status: completed
   - Description: WhatsApp and Twilio webhook endpoints dispatch to `ProcessWhatsAppWebhookJob` and `ProcessSmsWebhookJob` respectively. Job skeletons created under `app/Jobs/Channels`.
   - Next: Add provider signature verification tests and idempotency checks where applicable.

6. Email inbound pipeline
   - Status: completed
   - Description: Inbound webhook endpoint created (`EmailController::inbound`) and `ProcessInboundEmailJob` implemented to create `Contact`/`Conversation`/`Message` and store attachments.
   - Next: Integrate with provider-specific inbound flows (Mailgun/Postmark/SendGrid) and add signature verification.

7. Dialogflow/OpenAI integration
   - Status: in-progress
   - Description: Enqueueing pattern defined; added `ProcessOpenAiEnrichmentJob` skeleton to handle asynchronous message enrichment via OpenAI. Service layer (`app/Services/Integrations/OpenAIService.php`) should implement `enrichMessage` method.
   - Next: Implement `OpenAIService::enrichMessage`, wire message-created events to enqueue enrichment jobs, and add cost/latency metrics.

8. Article embeddings
   - Status: completed
   - Description: End-to-end embedding flow implemented. Migration, model, repository, service, and queued job added.
   - Files: `database/migrations/2025_12_30_000001_create_article_embeddings_table.php`, `app/Models/ArticleEmbedding.php`, `app/Repositories/ArticleEmbeddingRepo.php`, `app/Services/Articles/ArticleEmbeddingService.php`, `app/Jobs/Articles/GenerateArticleEmbeddingJob.php`.
   - Next: Wire article create/update events to dispatch `GenerateArticleEmbeddingJob` and add unit/integration tests for persistence and idempotency.

9. Data imports
   - Status: completed
   - Description: `data_imports` migration and `DataImport` model added. Repository exists to manage import lifecycle.
   - Files: `database/migrations/2025_12_30_000002_create_data_imports_table.php`, `app/Models/DataImport.php`, `app/Repositories/DataImportRepository.php`.
   - Next: Add import worker tests and UI hooks (upload endpoints).

10. SLA business-hours handling
   - Status: completed
   - Description: `CheckSlaJob` updated to compute deadlines respecting inbox working hours and timezone.
   - Files: `app/Jobs/Sla/CheckSlaJob.php`.
   - Next: Add tests to validate SLA deadline calculations across timezone/working-hours boundaries.

11. Remaining migration-model tasks
   - Status: in-progress
   - Description: Migration stubs added for several small tables; models/repositories pending.
   - Files: `database/migrations/2025_12_30_000003_create_email_templates_table.php`, `000004_create_leaves_table.php`, `000005_create_portal_members_table.php`, `000006_create_related_categories_table.php`.
   - Next: Implement `EmailTemplate`, `Leave`, `PortalMember` (pivot), and `RelatedCategory` models and repositories when those features are required.

12. Wire article lifecycle to embedding job
   - Status: not-started
   - Description: Add event/listener or model observer to dispatch `GenerateArticleEmbeddingJob` on article create/update.
   - Next: Implement listener, add tests for dispatch and idempotency.

13. Tests & CI validation
   - Status: not-started
   - Description: Add tests for new migrations, `ArticleEmbedding` flow, `DataImport` lifecycle, and SLA business-hours logic. Run full test-suite in CI.
   - Next: Create tests and update CI to run `php artisan migrate` and `php artisan test` for the custom/laravel folder.

8. Migrations audit
   - Status: not-started
   - Description: Compare Rails schema to Laravel migrations; ensure pivot tables, foreign keys, defaults and indexes are present.
   - Next: Run schema diff, prepare migrations for any missing constraints and defaults.

9. Events & Listeners
   - Status: not-started
   - Description: Audit all domain events (assignment/conversation/message lifecycle) and wire listeners to perform side-effects (notifications, metrics, webhooks).
   - Next: Create mapping matrix and add listeners under `app/Listeners` + register in `EventServiceProvider`.

10. Tests: assignment & integrations
   - Status: in-progress
   - Description: Add unit and integration tests for assignment logic, Shopify and Facebook webhook flows. A starting test for `AutoAssignConversationAction` exists and more tests are planned for webhook idempotency and mapping.
   - Next: Add Facebook webhook parsing tests and end-to-end assignment tests; run full test suite in CI.

Progress notes
--------------
- Assignment policies rework: completed (team/skills/capacity + eager-loading). See `app/Actions/Assignment/AutoAssignConversationAction.php`.
- Shopify: OAuth endpoints added and webhook job exists: `app/Jobs/Integrations/ProcessShopifyWebhookJob.php` and `ShopifyController` updates.
- Facebook: webhook endpoint verifies signatures and enqueues `app/Jobs/Channels/ProcessFacebookWebhookJob.php`. Job contains placeholder logging; mapping to domain objects is pending.

Version history
---------------
- 1.0.0 (2025-12-30): Initial migration checklist created; assignment and Shopify work completed; Facebook webhook enqueuing implemented.

Maintainers
-----------
- Keep this file current when launching or finishing tasks. Use the in-repo todo tracker (`manage_todo_list`) to keep the authoritative state.
