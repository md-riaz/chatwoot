# Feature Exclusions - AI/ML Components

## Excluded Features

The following features are **explicitly EXCLUDED** from the migration:

### ❌ Copilot
AI assistant functionality and related features.

### ❌ Captain
AI-powered features, responses, and document processing.

### ❌ AI/ML Components
Any machine learning or artificial intelligence features.

## Rationale for Exclusions

### 1. Specialized Infrastructure
AI-powered features require specialized infrastructure, models, and processing capabilities that are outside the scope of the current migration.

### 2. Complexity
AI features add significant complexity to both backend processing and frontend interfaces, which would delay the core migration.

### 3. External Dependencies
These features often depend on external AI services and specialized data processing pipelines that require separate integration efforts.

### 4. Focus on Core Functionality
Excluding AI features allows the migration to focus on core business functionality and user experience, ensuring a solid foundation before adding advanced features.

## Implementation Guidelines

### 1. Remove AI References
When migrating code, remove or comment out copilot/captain related functionality.

**Example:**
```php
// ❌ Remove this
if ($account->hasCopilotEnabled()) {
    $this->copilotService->process($message);
}

// ✅ Keep only core functionality
$this->messageService->process($message);
```

### 2. Feature Flags
Ensure AI features are disabled in feature flag configurations.

**Laravel:**
```php
// config/features.php
return [
    'copilot' => false,
    'captain' => false,
    'ai_responses' => false,
];
```

**Frontend:**
```typescript
// src/lib/config/features.ts
export const features = {
  copilot: false,
  captain: false,
  aiResponses: false,
};
```

### 3. Database
Skip migration of AI-related tables and data structures.

**Tables to skip:**
- `copilot_sessions`
- `copilot_messages`
- `captain_documents`
- `captain_responses`
- `ai_training_data`
- `ml_models`

### 4. Frontend
Exclude AI-related UI components and workflows.

**Components to skip:**
- `CopilotChat.svelte`
- `CaptainAssistant.svelte`
- `AIResponseSuggestions.svelte`
- `DocumentProcessor.svelte`

### 5. API Endpoints
Do not implement AI-related API endpoints.

**Endpoints to skip:**
- `/api/v1/copilot/*`
- `/api/v1/captain/*`
- `/api/v1/accounts/*/integrations/openai*`
- `/api/v1/ai/suggestions`
- `/api/v1/ml/predictions`

### 6. Documentation
Mark AI features as "Not Implemented" in API documentation.

**OpenAPI/Swagger:**
```yaml
paths:
  /api/v1/copilot:
    get:
      deprecated: true
      description: "Not implemented in Laravel migration"
```

## Code Review Checklist

When reviewing code, ensure:

- [ ] No copilot/captain imports or references
- [ ] No AI-related database queries
- [ ] No AI feature flags enabled
- [ ] No AI-related API endpoints
- [ ] No AI-related UI components
- [ ] No AI-related configuration
- [ ] No AI-related tests (unless testing exclusion)

## Migration Strategy

### Phase 1: Core Features (Current)
Migrate all non-AI features with full functionality.

### Phase 2: AI Features (Future)
After core migration is complete and stable, AI features can be:
1. Re-evaluated for implementation
2. Designed with Laravel/SvelteKit architecture
3. Implemented as separate modules
4. Integrated incrementally

## Identifying AI Code

Look for these patterns when reviewing Rails code:

### Backend (Rails)
```ruby
# Copilot-related
Copilot::Session
CopilotService
copilot_enabled?

# Captain-related
Captain::Document
CaptainService
captain_process

# AI/ML-related
AiResponse
MlModel
ai_suggestion
```

### Frontend (Vue)
```javascript
// Copilot-related
import Copilot from '@/components/Copilot'
useCopilot()
copilotStore

// Captain-related
import Captain from '@/components/Captain'
useCaptain()
captainStore

// AI-related
aiSuggestions
mlPredictions
```

## Testing Exclusions

Write tests to ensure AI features are properly excluded:

```php
// tests/Feature/FeatureExclusionTest.php
test('copilot feature is disabled', function () {
    expect(config('features.copilot'))->toBeFalse();
});

test('captain endpoints return 404', function () {
    $response = $this->getJson('/api/v1/captain/sessions');
    $response->assertNotFound();
});

test('ai feature flags are disabled', function () {
    $account = Account::factory()->create();
    expect($account->hasFeature('copilot'))->toBeFalse();
    expect($account->hasFeature('captain'))->toBeFalse();
});
```

## Future Considerations

When AI features are ready to be implemented:

1. **Architecture Review**: Design AI features with Laravel/SvelteKit patterns
2. **Separate Module**: Implement as a separate, optional module
3. **Feature Flags**: Use feature flags for gradual rollout
4. **Documentation**: Comprehensive documentation for AI features
5. **Testing**: Extensive testing including edge cases
6. **Performance**: Monitor performance impact
7. **Security**: Review security implications
8. **Privacy**: Ensure compliance with data privacy regulations

## Questions?

If you encounter AI-related code during migration:

1. **Skip it**: Don't migrate AI features
2. **Document it**: Note the skipped feature
3. **Remove references**: Clean up any dependencies
4. **Test exclusion**: Verify feature is properly excluded
5. **Ask if unsure**: Consult team if unclear whether feature is AI-related
