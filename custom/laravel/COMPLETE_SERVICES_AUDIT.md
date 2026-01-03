# Complete Services Audit - AGENTS.md Compliance

## Current Status: INCOMPLETE ❌

The previous reorganization was only partial. Many services still exist and need to be reorganized according to AGENTS.md guidelines.

## 🔍 Complete Service Inventory

### Loose Services (Root Level) - 14 files
- `AgentCapacityService.php` ✅ Action created, needs removal
- `ConfigCacheService.php` ✅ Action created, needs removal  
- `ConfigLoaderService.php` ✅ Integrated into ManageCacheAction, needs removal
- `DatabaseOptimizationService.php` ✅ Action created, needs removal
- `FeatureFlagService.php` ✅ Action created, needs removal
- `FilterService.php` ✅ Action created, needs removal
- `GlobalConfigService.php` ✅ Integrated into ConfigRepository, needs removal
- `IpLookupService.php` ✅ Action created, needs removal
- `PageCrawlerService.php` ❌ No Action created yet
- `PermissionFilterService.php` ✅ Action created, needs removal
- `QueueOptimizationService.php` ✅ Action created, needs removal
- `SearchPerformanceService.php` ✅ Integrated into PerformSearchAction, needs removal
- `SearchService.php` ✅ Action created, needs removal
- `TranslationService.php` ✅ Action created, needs removal

### Subdirectory Services - 25+ files
#### Articles Domain (1 file)
- `Articles/ArticleEmbeddingService.php` ❌ Needs Action

#### Auth Domain (4 files)
- `Auth/BaseRefreshOauthTokenService.php` ❌ Needs Action
- `Auth/BaseTokenService.php` ❌ Needs Action
- `Auth/GoogleRefreshOauthTokenService.php` ❌ Needs Action
- `Auth/MicrosoftRefreshOauthTokenService.php` ❌ Needs Action

#### Channel Domain (12+ files)
- `Channels/BaseSendOnChannelService.php` ❌ Needs Action
- `Channels/InboundMessageService.php` ❌ Needs Action
- `Channels/Email/BounceHandlingService.php` ❌ Needs Action
- `Channels/Email/InboundEmailProcessor.php` ❌ Needs Action
- `Channels/Email/LiquidTemplateService.php` ❌ Needs Action
- `Channels/Email/TemplateResolverService.php` ❌ Needs Action
- `Channels/Facebook/FacebookService.php` ❌ Needs Action
- `Channels/Facebook/SendOnFacebookService.php` ❌ Needs Action
- `Channels/Instagram/SendOnInstagramService.php` ❌ Needs Action
- `Channels/Whatsapp/SendOnWhatsappService.php` ❌ Needs Action
- `Channels/Whatsapp/WebhookSetupService.php` ❌ Needs Action
- And many more in subdirectories...

#### Integration Domain (5 files)
- `Integrations/DialogflowService.php` ❌ Needs Action
- `Integrations/LinearService.php` ❌ Needs Action
- `Integrations/OpenAIService.php` ❌ Needs Action
- `Integrations/ShopifyService.php` ❌ Needs Action
- `Integrations/SlackService.php` ❌ Needs Action

#### Message Domain (1 file)
- `Messages/AudioTranscriptionService.php` ❌ Needs Action

#### Voice Domain (3 files)
- `Voice/CallSessionSyncService.php` ❌ Needs Action
- `Voice/CallStatusManager.php` ❌ Needs Action
- `Voice/StatusUpdateService.php` ❌ Needs Action

#### HTTP Domain (2 files)
- `Http/OAuth1Client.php` ❌ Needs Action
- `Http/RetryableHttpClient.php` ❌ Needs Action

## 🚨 Critical Issues Found

### 1. Old Services Still Referenced
Many controllers, models, and other services still reference the old service classes:
- Tests still use old service classes
- Controllers still instantiate old services
- Models still reference old services
- Service provider still binds old services

### 2. Circular Dependencies
Some new Actions still reference old Services:
- `PerformSearchAction` uses `PermissionFilterService`
- `SearchRepository` uses `PermissionFilterService`

### 3. Missing Actions
Many services don't have corresponding Actions yet, especially in subdirectories.

## 📋 Immediate Action Plan

### Phase 1: Fix Circular Dependencies
1. Update `PerformSearchAction` to use `ApplyPermissionFiltersAction`
2. Update `SearchRepository` to use `FilterRepository`
3. Remove old service references from new Actions

### Phase 2: Complete Missing Actions
1. Create `CrawlPageAction` for `PageCrawlerService`
2. Create Actions for all subdirectory services

### Phase 3: Update All References
1. Update all test files to use new Actions
2. Update all controllers to use new Actions
3. Update all models to use new Actions
4. Update service providers

### Phase 4: Remove Old Services
1. Delete all old service files
2. Verify no broken references remain

## 🎯 Target Architecture

Every service should follow this pattern:
```
Domain/
├── Actions/
│   ├── ManageSomethingAction.php
│   └── ProcessSomethingAction.php
├── Repositories/
│   └── SomethingRepository.php
└── Data/
    └── SomethingData.php
```

## 📊 Current Progress

- **Actions Created**: 10/40+ (25%)
- **References Updated**: 5/100+ (5%)
- **Old Services Removed**: 0/40+ (0%)
- **AGENTS.md Compliance**: 25%

## ⚠️ Status: MAJOR WORK REMAINING

The reorganization is far from complete. Significant work is needed to achieve full AGENTS.md compliance.