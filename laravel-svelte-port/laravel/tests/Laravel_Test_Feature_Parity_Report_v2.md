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

If you want, I can now (1) paste the raw failing test output into this report, (2) triage a specific failing test file and propose fixes, or (3) convert deprecated PHPUnit metadata to attributes across tests. Which would you like me to do next?