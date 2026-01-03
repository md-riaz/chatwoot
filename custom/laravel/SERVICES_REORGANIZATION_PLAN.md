# Services Reorganization Plan

## Current Issue
The Laravel app has many loose service files in `app/Services/` that don't follow AGENTS.md guidelines. These should be reorganized into domain-based Actions following the Action → Repository → Model pattern.

## Reorganization Strategy

### 1. Agent Domain Services
**Current Files:**
- `AgentCapacityService.php`

**New Structure:**
- `app/Actions/Agent/ManageCapacityAction.php`
- `app/Repositories/Agent/CapacityRepository.php`
- `app/Data/Agent/CapacityData.php`

### 2. Search Domain Services
**Current Files:**
- `SearchService.php`
- `SearchPerformanceService.php`

**New Structure:**
- `app/Actions/Search/PerformSearchAction.php`
- `app/Actions/Search/OptimizePerformanceAction.php`
- `app/Repositories/Search/SearchRepository.php`
- `app/Data/Search/SearchQueryData.php`

### 3. Filter Domain Services
**Current Files:**
- `FilterService.php`
- `PermissionFilterService.php`

**New Structure:**
- `app/Actions/Filter/ApplyFiltersAction.php`
- `app/Actions/Filter/ApplyPermissionFiltersAction.php`
- `app/Repositories/Filter/FilterRepository.php`
- `app/Data/Filter/FilterData.php`

### 4. Configuration Domain Services
**Current Files:**
- `ConfigCacheService.php`
- `ConfigLoaderService.php`
- `GlobalConfigService.php`
- `FeatureFlagService.php`

**New Structure:**
- `app/Actions/Config/ManageCacheAction.php`
- `app/Actions/Config/LoadConfigAction.php`
- `app/Actions/Config/ManageFeatureFlagsAction.php`
- `app/Repositories/Config/ConfigRepository.php`
- `app/Data/Config/ConfigData.php`

### 5. System Domain Services
**Current Files:**
- `DatabaseOptimizationService.php`
- `QueueOptimizationService.php`
- `IpLookupService.php`
- `PageCrawlerService.php`

**New Structure:**
- `app/Actions/System/OptimizeDatabaseAction.php`
- `app/Actions/System/OptimizeQueueAction.php`
- `app/Actions/System/LookupIpAction.php`
- `app/Actions/System/CrawlPageAction.php`
- `app/Repositories/System/SystemRepository.php`

### 6. Translation Domain Services
**Current Files:**
- `TranslationService.php`

**New Structure:**
- `app/Actions/Translation/TranslateTextAction.php`
- `app/Repositories/Translation/TranslationRepository.php`
- `app/Data/Translation/TranslationData.php`

## Implementation Steps

1. Create domain directories under `app/Actions/`
2. Convert each service to Action pattern with proper dependency injection
3. Create corresponding Repository classes for data access
4. Create Data objects for type-safe request/response handling
5. Update all references to use new Actions
6. Remove old service files
7. Update service provider bindings if needed

## Benefits

- **Domain Organization**: Services grouped by business domain
- **Consistent Architecture**: All follow Action → Repository → Model pattern
- **Better Testability**: Actions are easier to test in isolation
- **Type Safety**: Data objects provide better type checking
- **Dependency Injection**: Proper Laravel service container usage
- **Maintainability**: Clear separation of concerns

## Files to be Removed After Migration

- `app/Services/AgentCapacityService.php`
- `app/Services/ConfigCacheService.php`
- `app/Services/ConfigLoaderService.php`
- `app/Services/DatabaseOptimizationService.php`
- `app/Services/FeatureFlagService.php`
- `app/Services/FilterService.php`
- `app/Services/GlobalConfigService.php`
- `app/Services/IpLookupService.php`
- `app/Services/PageCrawlerService.php`
- `app/Services/PermissionFilterService.php`
- `app/Services/QueueOptimizationService.php`
- `app/Services/SearchPerformanceService.php`
- `app/Services/SearchService.php`
- `app/Services/TranslationService.php`