# Laravel Test Feature Parity Report — v2 (2026-01-10)

This report documents results from running the Laravel test suite and analyzes feature/functional parity with the Rails backend. It focuses on whether Laravel tests cover the same business features (not implementation parity), which tests fail, why they fail, and recommended next steps.

Command run (WSL Debian):

```bash
cd /mnt/c/projects/chatwoot/laravel-svelte-port/laravel
vendor/bin/phpunit --testdox --colors=never
```

Summary of results (selected):
- Several test groups passed (onboarding, basic account and user management, many API endpoints).
- Multiple test suites show failures indicating incomplete feature parity or mismatched expectations.

Key failing test suites (observed from run):

1) Tests\Feature\Actions\Agent\ManageCapacityActionTest
- Status: FAIL (multiple assertions)
- Scope: Agent availability, exclusion rules, conversation assignment, capacity statistics, validation rules.
- Likely causes: Business logic for capacity/exclusion rules not fully migrated or differing from Rails; tests expect Rails semantics.
- Impact: Conversation assignment and agent availability features may behave differently from Rails in edge cases.

2) Tests\Feature\Actions\Search\PerformSearchActionTest
- Status: FAIL (multiple assertions)
- Scope: Searching messages, conversations, contacts; filters, caching, special-character handling, limits.
- Likely causes: Search implementation differs (indexing, filtering, privacy rules) or tests assume Rails search behavior.
- Impact: Search features may not return same results or respect same filters as Rails.

3) Tests\Feature\Api\Accounts\AccountsCrudTest and Tests\Feature\Api\AccountsTest
- Status: Partial FAILs (account creation/update and some edge cases)
- Scope: Account creation with optional fields, unicode/null handling, updates and validation.
- Likely causes: Validation rules or request handling differs; charset/locale handling or optional-field defaults differ from Rails.
- Impact: Account creation flows and edge-case data may behave differently.

4) Tests\Feature\Api\AgentBots\AgentBotsCrudTest
- Status: Partial FAILs (inbox association tests failing)
- Scope: Agent bot listing/creation/update/delete; association of bots to inboxes.
- Likely causes: Association endpoints or background handling of bot<>inbox relations differ from Rails.
- Impact: Feature parity lacking for agent bot-inbox wiring.

5) Tests\Feature\Api\AgentCapacityPolicies\AgentCapacityPoliciesCrudTest
- Status: Partial FAILs (listing, validation, inbox limits)
- Scope: CRUD + validation for capacity policies, inbox-specific capacity limits, user assignments to policies.
- Likely causes: Validation rules and inbox capacity semantics differ or are incomplete in Laravel implementation.
- Impact: Agent capacity policy features may not enforce rules the same way as Rails.

Other observations
- Many tests include PHPUnit doc-comment metadata which is deprecated and will be removed in PHPUnit 12; migrate to attributes to avoid future breakage.
- No AI (Copilot/Captain) feature tests were present — correct per migration scope.

Representative failing assertions (from test run):
- Several tests in ManageCapacityActionTest and PerformSearchActionTest failed across many assertions (see full test run output available in CI logs or local terminal).
- In Accounts tests, creation with optional fields and unicode handling produced failures for specific test cases.

Recommendations
- Prioritize feature-parity fixes for:
  - Agent capacity logic and exclusion rules
  - Search indexing/filters/privacy behavior
  - Account creation/validation parity (optional fields, unicode/null handling)
  - Agent bot <> inbox association
  - Agent capacity policies validation and inbox limits
- Update failing tests only after confirming intended Rails-compatible behavior; do not change tests to match temporary implementation differences.
- Convert doc-comment metadata in tests to PHPUnit attributes for future compatibility.
- Add focused integration checks comparing Rails API responses with Laravel for the failing areas (sample requests/expected responses).

Next steps I can take (pick any):
- Open and paste specific failing test outputs and stack traces into the report.
- Triage one failing test file (e.g., `Tests\Feature\Actions\Search\PerformSearchActionTest`) and propose concrete code changes.
- Convert doc-comment metadata in tests to attributes across the codebase.

Files created/modified
- Created: `tests/Laravel_Test_Feature_Parity_Report_v2.md`


---

# Raw failing test output (captured)

I re-ran the test suite with Pest and captured the console output. Below is the raw output (warnings, PASS/FAIL summaries and failing test names). I trimmed long repetitive warning sections with an explicit placeholder, but included the failing suites and representative failing assertions.

```
WARN  Metadata found in doc-comment for method Tests\Unit\Actions\SuperAdmin\CalculateDashboardMetricsActionTest::it_returns_dashboard_data_object(). Metadata in doc-comments is deprecated and will no longer be supported in PHPUnit 12. Update your test code to use attributes instead.

... (many similar deprecation warnings omitted for brevity) ...

PHPUnit 11.5.33 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.16
Configuration: /mnt/c/projects/chatwoot/laravel-svelte-port/laravel/phpunit.xml

FAIL  Tests\Unit\Actions\AutoAssignConversationActionTest
 ⨯ auto assign dispatches ConversationAssigned                               0.58s

FAIL  Tests\Unit\Actions\Conversation\ManageParticipantsActionTest
 ⨯ ManageParticipantsAction → can get participants for conversation
 ⨯ ManageParticipantsAction → can add participants to conversation
 ⨯ ManageParticipantsAction → prevents duplicate participants when adding
 ⨯ ManageParticipantsAction → can update participants for conversation
 ⨯ ManageParticipantsAction → can remove participants from conversation
 ...

FAIL  Tests\Unit\Actions\Conversations\ManageDraftMessageActionTest
 ⨯ ManageDraftMessageAction → getDraft → returns null when no draft exists
 ⨯ ManageDraftMessageAction → getDraft → returns draft data when draft exists
 ⨯ ManageDraftMessageAction → saveDraft → saves draft message successfully
 ⨯ ManageDraftMessageAction → saveDraft → overwrites existing draft
 ...

PASS  Tests\Unit\Actions\SuperAdmin\CalculateDashboardMetricsActionTest
 ✓ it returns dashboard data object                                          0.05s
 ✓ it has correct structure                                                  0.03s
 ...

FAIL  Tests\Unit\Jobs\JobsTest
 ⨯ Auto Resolve Conversation Job → resolves stale open conversations         0.03s
 ⨯ Auto Resolve Conversation Job → does not resolve active conversations     0.03s
 ...

FAIL  Tests\Feature\Api\Accounts\AccountsCrudTest
 ✓ Account Listing → authenticated user can list their accounts              3.62s
 ✓ Account Listing → user sees only their associated accounts                0.23s
 ✓ Account Listing → accounts list returns paginated results                 0.13s
 ⨯ Account Creation → authenticated user can create an account               3.00s
 ⨯ Account Creation → account creation with all optional fields              0.52s
 ⨯ Account Update → admin can update account                                 0.26s
 ...

FAIL  Tests\Feature\Actions\Agent\ManageCapacityActionTest
 ⨯ ManageCapacityAction - Available Agents → returns available agents for…  12.20s
 ⨯ ManageCapacityAction - Available Agents → excludes agents at capacity li… 0.04s
 ⨯ ManageCapacityAction - Conversation Assignment → excludes conversations…  0.04s
 ⨯ ManageCapacityAction - Validation → validates exclusion rules correctly   0.21s
 ...

FAIL  Tests\Feature\Actions\Search\PerformSearchActionTest
 ⨯ PerformSearchAction → can search messages
 ⨯ PerformSearchAction → can search conversations
 ⨯ PerformSearchAction → can search contacts
 ⨯ PerformSearchAction → can perform comprehensive search
 ⨯ PerformSearchAction → filters messages by time window
 ⨯ PerformSearchAction → excludes private messages from search
 ...

FAIL  Tests\Feature\Api\AgentBots\AgentBotsCrudTest
 ⨯ Agent Bot Inbox Association → can associate bot with inbox via set_agent… 0.05s
 ⨯ Agent Bot Inbox Association → can remove bot from inbox via set_agent_bo… 0.05s
 ...

FAIL  Tests\Feature\Api\AgentCapacityPolicies\AgentCapacityPoliciesCrudTest
 ⨯ Agent Capacity Policy Listing → can list agent capacity policies for acc… 0.08s
 ⨯ Agent Capacity Policy Validation → validates exclusion rules on creation  0.04s
 ⨯ Agent Capacity Policy Validation → validates exclusion rules on update    0.06s
 ...

... (additional failing unit and feature tests continue in output) ...

```

If you want the full, untruncated terminal output saved verbatim into the report file, I can append the entire run log (it will be large). Alternatively I can paste only the failing suites and their full stack traces for the top-N failing suites you care about (recommended). Which do you prefer?
