# Full Parity Specification: Vue Frontend → Svelte SPA

## Executive Summary

**Current Status**: ~70-75% parity  
**Target**: 100% feature and route parity  
**Estimated Effort**: 9-13 weeks (360-520 hours)  
**Priority**: Replace Vue frontend entirely with Svelte SPA

This document provides a complete specification to achieve full UI/UX and route parity between the Vue frontend (`app/javascript/dashboard`) and Svelte SPA (`custom/ui/svelte-ui`), enabling complete replacement of the Vue frontend.

---

## Current State Analysis

### ✅ Complete (Phases 0-5)
- **Phase 0**: Foundation (routing, auth, API client)
- **Phase 1**: Core State Management (auth, conversations, messages, contacts, inboxes, teams, labels)
- **Phase 2**: Core UI Components (conversations, messages, contacts, sidebar, header)
- **Phase 3**: Dashboard Pages (conversations, contacts, inbox views)
- **Phase 4**: Widget, Portal, Survey, SuperAdmin
- **Phase 5**: Advanced Features (automation, macros, notifications, search, reports, SLA, audit logs)

### 🚧 Phase 6 In Progress (21%)
- Task 6.1: Unit Testing Infrastructure ✅
- Task 6.2: Component Testing (25%)
- Tasks 6.3-6.7: Pending

### ❌ Missing Features (Phase 8-10)
- **Phase 8**: Campaigns, Companies, Settings Pages (25-30%)
- **Phase 9**: Advanced Settings & Configuration (0%)
- **Phase 10**: Remaining Features & Polish (0%)

---

## Phase 8: Campaigns, Companies & Core Settings (8-10 weeks)

### 8.1: Campaigns Module (2-3 weeks)

**Priority**: HIGH  
**Estimated Time**: 80-120 hours  
**Complexity**: High

#### Context
Campaigns enable marketing automation via email, SMS, WhatsApp, and live chat. Missing entirely from Svelte UI.

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/campaigns/`
  - `campaigns.routes.js`
  - `Index.vue` - Campaign list
  - `AddCampaign.vue` - Create campaign
  - `EditCampaign.vue` - Edit campaign
- `app/javascript/dashboard/store/modules/campaigns.js`

#### Implementation Steps

**Step 1: Campaigns Store** (8-10 hours)
```typescript
// src/lib/stores/campaigns.svelte.ts
import { writable } from 'svelte/store';
import type { Campaign, CampaignPayload } from '$lib/types';

class CampaignsStore {
  campaigns = $state<Campaign[]>([]);
  loading = $state(false);
  selectedCampaign = $state<Campaign | null>(null);

  async fetchCampaigns() { /* ... */ }
  async createCampaign(payload: CampaignPayload) { /* ... */ }
  async updateCampaign(id: number, payload: CampaignPayload) { /* ... */ }
  async deleteCampaign(id: number) { /* ... */ }
  async toggleCampaignStatus(id: number) { /* ... */ }
}
```

**Step 2: Campaigns API Client** (6-8 hours)
```typescript
// src/lib/api/campaigns.ts
export const campaignsApi = {
  list: (inboxId?: number) => client.get('/campaigns'),
  get: (id: number) => client.get(`/campaigns/${id}`),
  create: (data: CampaignPayload) => client.post('/campaigns', data),
  update: (id: number, data: CampaignPayload) => 
    client.patch(`/campaigns/${id}`, data),
  delete: (id: number) => client.delete(`/campaigns/${id}`),
  toggleStatus: (id: number) => client.post(`/campaigns/${id}/toggle_status`),
};
```

**Step 3: Campaign Types** (4-6 hours)
```typescript
// src/lib/types/campaign.ts
export interface Campaign {
  id: number;
  title: string;
  description?: string;
  message: string;
  campaign_type: 'one_off' | 'ongoing';
  campaign_status: 'active' | 'paused' | 'completed';
  audience: CampaignAudience[];
  enabled: boolean;
  trigger_rules: TriggerRule;
  inbox_id: number;
  sender_id?: number;
  scheduled_at?: string;
  created_at: string;
  updated_at: string;
}

export interface CampaignAudience {
  type: 'label' | 'country' | 'city' | 'browser_language';
  values: string[];
}

export interface TriggerRule {
  time_on_page?: number;
  url?: string;
}
```

**Step 4: Campaign List Page** (16-20 hours)
```svelte
<!-- src/routes/app/campaigns/+page.svelte -->
<script lang="ts">
  import { campaigns } from '$lib/stores/campaigns.svelte';
  import CampaignCard from '$lib/components/campaigns/CampaignCard.svelte';
  import { Button } from '$lib/components/ui/button';
  
  $effect(() => {
    campaigns.fetchCampaigns();
  });
</script>

<div class="campaigns-container">
  <header>
    <h1>Campaigns</h1>
    <Button href="/app/campaigns/new">Create Campaign</Button>
  </header>
  
  <div class="campaigns-grid">
    {#each campaigns.campaigns as campaign}
      <CampaignCard {campaign} />
    {/each}
  </div>
</div>
```

**Step 5: Create/Edit Campaign Forms** (24-32 hours)
- Campaign details form
- Audience selector
- Message composer
- Schedule picker
- Trigger rules configuration

**Step 6: Campaign Components** (16-20 hours)
- CampaignCard.svelte
- CampaignForm.svelte
- AudienceSelector.svelte
- TriggerRulesEditor.svelte
- CampaignStats.svelte

**Step 7: Campaign Routes** (6-8 hours)
```typescript
// Add to src/routes/app/campaigns/
// +page.svelte (list)
// new/+page.svelte (create)
// [id]/+page.svelte (view)
// [id]/edit/+page.svelte (edit)
```

#### Acceptance Criteria
- ✅ List all campaigns with filtering
- ✅ Create new campaigns (one-off, ongoing)
- ✅ Edit existing campaigns
- ✅ Delete campaigns
- ✅ Toggle campaign status (active/paused)
- ✅ Configure audience targeting
- ✅ Set trigger rules
- ✅ Schedule campaigns
- ✅ View campaign stats

#### Validation
```bash
npm run dev
# Navigate to /app/campaigns
# Create, edit, delete campaigns
# Verify API calls match Vue implementation
```

---

### 8.2: Companies/Organizations Module (1.5-2 weeks)

**Priority**: HIGH  
**Estimated Time**: 60-80 hours  
**Complexity**: Medium

#### Context
Companies allow grouping contacts by organization for B2B workflows. Missing entirely.

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/contacts/components/ContactsTable.vue`
- `app/javascript/dashboard/store/modules/companies.js` (if exists)

#### Implementation Steps

**Step 1: Companies Store** (8-10 hours)
```typescript
// src/lib/stores/companies.svelte.ts
class CompaniesStore {
  companies = $state<Company[]>([]);
  loading = $state(false);
  
  async fetchCompanies() { /* ... */ }
  async createCompany(payload: CompanyPayload) { /* ... */ }
  async updateCompany(id: number, payload: CompanyPayload) { /* ... */ }
  async deleteCompany(id: number) { /* ... */ }
}
```

**Step 2: Companies API** (4-6 hours)
**Step 3: Company Types** (4-6 hours)
**Step 4: Companies List** (12-16 hours)
**Step 5: Company Detail View** (12-16 hours)
**Step 6: Create/Edit Forms** (12-16 hours)
**Step 7: Company-Contact Association** (8-10 hours)

#### Acceptance Criteria
- ✅ List companies with search/filter
- ✅ Create/edit/delete companies
- ✅ View company details with contacts
- ✅ Associate contacts with companies
- ✅ Company custom attributes

---

### 8.3: Account Settings Pages (1.5-2 weeks)

**Priority**: HIGH  
**Estimated Time**: 60-80 hours  
**Complexity**: Medium

#### Context
Account-level settings for profile, notifications, billing, general preferences. Partially missing.

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/account/`
- `app/javascript/dashboard/routes/dashboard/settings/profile/`
- `app/javascript/dashboard/routes/dashboard/settings/billing/`

#### Implementation Steps

**Step 1: Account General Settings** (12-16 hours)
- Account name, language, timezone
- Business hours
- Auto-resolve timeout
- Features toggles

**Step 2: Profile Settings** (8-10 hours)
- User avatar, name, email
- Password change
- Notification preferences
- Display density

**Step 3: Billing Settings** (12-16 hours)
- Subscription plan
- Payment method
- Invoice history
- Usage stats

**Step 4: Notification Preferences UI** (12-16 hours)
- Email notifications
- Push notifications
- In-app notifications
- Per-channel settings

**Step 5: Settings Layout** (8-10 hours)
- Sidebar navigation
- Settings header
- Breadcrumbs
- Save/cancel actions

**Step 6: Routes** (8-10 hours)
```
/app/settings/account/general
/app/settings/account/billing
/app/settings/profile
/app/settings/notifications
```

#### Acceptance Criteria
- ✅ Update account settings
- ✅ Update profile information
- ✅ View billing information
- ✅ Configure notification preferences
- ✅ Settings persist correctly

---

### 8.4: Agent Management Pages (1-1.5 weeks)

**Priority**: HIGH  
**Estimated Time**: 40-60 hours  
**Complexity**: Medium

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/agents/`

#### Implementation Steps

**Step 1: Agents List** (12-16 hours)
- List all agents
- Filter by status, role
- Bulk actions

**Step 2: Add/Edit Agent** (12-16 hours)
- Agent form
- Role assignment
- Team assignment
- Availability status

**Step 3: Agent Detail View** (8-10 hours)
- Agent profile
- Stats (conversations, response time)
- Activity log

**Step 4: Routes** (8-10 hours)
```
/app/settings/agents
/app/settings/agents/new
/app/settings/agents/[id]
```

#### Acceptance Criteria
- ✅ List agents with filters
- ✅ Add new agents
- ✅ Edit agent details
- ✅ Remove agents
- ✅ View agent statistics

---

### 8.5: Inbox Configuration Pages (1.5-2 weeks)

**Priority**: HIGH  
**Estimated Time**: 60-80 hours  
**Complexity**: High

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/inbox/`

#### Implementation Steps

**Step 1: Inbox List** (10-12 hours)
**Step 2: Create Inbox Wizard** (20-24 hours)
- Channel type selector
- Configuration forms (website, email, API, social)
- Widget customization
- Agent assignment

**Step 3: Inbox Settings** (16-20 hours)
- General settings
- Collaborators
- Pre-chat form
- Business hours
- CSAT survey

**Step 4: Channel-Specific Settings** (14-18 hours)
- Website widget (appearance, behavior)
- Email (IMAP/SMTP, forwarding)
- WhatsApp/SMS configuration
- Social media integration

#### Acceptance Criteria
- ✅ Create inbox for all channel types
- ✅ Configure inbox settings
- ✅ Customize widget appearance
- ✅ Set up pre-chat forms
- ✅ Configure CSAT surveys

---

### 8.6: Custom Attributes (1 week)

**Priority**: MEDIUM  
**Estimated Time**: 40-50 hours  
**Complexity**: Medium

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/attributes/`

#### Implementation Steps

**Step 1: Attributes Store** (6-8 hours)
**Step 2: Attributes API** (4-6 hours)
**Step 3: Attributes List** (10-12 hours)
**Step 4: Create/Edit Attribute** (12-16 hours)
- Attribute type (text, number, date, list)
- Apply to (conversation, contact)
- Validation rules

**Step 5: Routes** (8-10 hours)

#### Acceptance Criteria
- ✅ List custom attributes
- ✅ Create attributes for contacts/conversations
- ✅ Edit attribute configuration
- ✅ Delete attributes
- ✅ Attributes appear in contact/conversation forms

---

## Phase 9: Advanced Settings & Configuration (3-4 weeks)

### 9.1: Custom Roles & Permissions (1-1.5 weeks)

**Priority**: MEDIUM  
**Estimated Time**: 40-60 hours  
**Complexity**: High

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/customRoles/`

#### Implementation Steps

**Step 1: Roles Store** (8-10 hours)
**Step 2: Roles API** (6-8 hours)
**Step 3: Roles List** (10-12 hours)
**Step 4: Create/Edit Role** (16-20 hours)
- Permission matrix
- Role name/description
- Assign to agents

#### Acceptance Criteria
- ✅ List custom roles
- ✅ Create roles with permissions
- ✅ Edit role permissions
- ✅ Assign roles to agents
- ✅ Delete roles

---

### 9.2: Assignment Policy (1 week)

**Priority**: MEDIUM  
**Estimated Time**: 40-50 hours  
**Complexity**: Medium

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/`

#### Implementation Steps

**Step 1: Policy Settings** (16-20 hours)
- Auto-assignment toggle
- Assignment strategy (round-robin, load-based, skill-based)
- Re-assignment rules

**Step 2: Priority Rules** (12-16 hours)
**Step 3: Availability Rules** (12-16 hours)

#### Acceptance Criteria
- ✅ Configure auto-assignment
- ✅ Set assignment strategy
- ✅ Define priority rules
- ✅ Configure availability rules

---

### 9.3: Security Settings (1 week)

**Priority**: MEDIUM  
**Estimated Time**: 40-50 hours

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/security/`

#### Implementation Steps

**Step 1: Two-Factor Auth** (16-20 hours)
**Step 2: Session Management** (12-16 hours)
**Step 3: IP Allowlist** (12-16 hours)

---

### 9.4: Integrations Pages (1 week)

**Priority**: LOW  
**Estimated Time**: 40-50 hours

#### Vue Reference Files
- `app/javascript/dashboard/routes/dashboard/settings/integrations/`

#### Implementation Steps

**Step 1: Integrations List** (12-16 hours)
**Step 2: Integration Configuration** (20-24 hours)
**Step 3: Webhooks** (8-10 hours)

---

## Phase 10: Polish & Remaining Features (2-3 weeks)

### 10.1: Custom Views (1 week)

**Priority**: LOW  
**Estimated Time**: 40-50 hours

#### Implementation
- Custom filters
- Saved views
- View sharing

---

### 10.2: Captain (AI Assistant) (1 week)

**Priority**: LOW  
**Estimated Time**: 40-50 hours

#### Implementation
- AI suggestions
- Auto-responses
- Smart replies

---

### 10.3: Global Search UI (0.5 week)

**Priority**: MEDIUM  
**Estimated Time**: 20-30 hours

#### Implementation
- Search command palette
- Results rendering
- Quick actions

---

### 10.4: Reports UI Enhancement (0.5-1 week)

**Priority**: MEDIUM  
**Estimated Time**: 20-40 hours

#### Implementation
- Charts and graphs
- Export functionality
- Date range selection
- Filter options

---

## Implementation Strategy

### Phase Order
1. **Phase 8**: Campaigns, Companies, Settings (8-10 weeks) - CRITICAL
2. **Phase 9**: Advanced Settings (3-4 weeks) - HIGH
3. **Phase 10**: Polish & Remaining (2-3 weeks) - MEDIUM
4. **Phase 6 Completion**: Testing (4-6 weeks) - PARALLEL
5. **Phase 7**: Documentation (1-2 weeks) - FINAL

### Team Structure (Recommended)
- **2 Senior Developers**: Campaigns, Companies, Complex Settings
- **2 Mid-Level Developers**: Settings Pages, UI Components
- **1 QA Engineer**: Testing, validation (parallel with Phase 6)

### Timeline
- **Optimistic**: 13 weeks (3.25 months)
- **Realistic**: 17 weeks (4.25 months)
- **Conservative**: 20 weeks (5 months)

---

## Success Metrics

### Feature Parity
- ✅ 100% route coverage (150-200 routes)
- ✅ 100% functionality coverage
- ✅ All Vue features replicated

### Code Quality
- ✅ >80% test coverage
- ✅ 0 critical accessibility issues
- ✅ Lighthouse score >90
- ✅ Bundle size <500KB gzipped

### Performance
- ✅ Initial load <2s
- ✅ Route transitions <100ms
- ✅ Core Web Vitals passing

### User Experience
- ✅ Identical UI/UX to Vue
- ✅ All keyboard shortcuts working
- ✅ Mobile responsive
- ✅ Dark mode support

---

## Migration Plan

### Stage 1: Feature Freeze (Week 0)
- Lock Vue frontend features
- Document all routes and components
- Create migration checklist

### Stage 2: Development (Weeks 1-17)
- Implement Phases 8-10
- Parallel testing (Phase 6)
- Weekly demos and validation

### Stage 3: Testing (Weeks 15-19)
- E2E testing
- User acceptance testing
- Performance testing
- Security audit

### Stage 4: Soft Launch (Week 20)
- Beta flag for Svelte UI
- Selected users testing
- Bug fixes and refinement

### Stage 5: Full Rollout (Week 22)
- Enable Svelte UI by default
- Monitor metrics
- Deprecate Vue frontend

### Stage 6: Cleanup (Week 24)
- Remove Vue code
- Archive `app/javascript/dashboard`
- Update documentation

---

## Risk Mitigation

### Technical Risks
1. **API Compatibility**: Ensure all API endpoints work with Svelte
2. **Real-time Features**: WebSocket integration must be solid
3. **Browser Support**: Test across all supported browsers

### Mitigation Strategies
- Daily API integration testing
- WebSocket stress testing
- Cross-browser testing automation
- Feature flags for gradual rollout

---

## Appendix A: Complete Route Mapping

### Vue Routes → Svelte Routes

| Vue Route | Svelte Route | Status | Priority |
|-----------|--------------|--------|----------|
| `/app/campaigns` | `/app/campaigns` | ❌ Missing | HIGH |
| `/app/campaigns/new` | `/app/campaigns/new` | ❌ Missing | HIGH |
| `/app/campaigns/:id` | `/app/campaigns/[id]` | ❌ Missing | HIGH |
| `/app/contacts/companies` | `/app/companies` | ❌ Missing | HIGH |
| `/app/settings/account` | `/app/settings/account` | ❌ Missing | HIGH |
| `/app/settings/agents` | `/app/settings/agents` | ❌ Missing | HIGH |
| `/app/settings/inboxes` | `/app/settings/inboxes` | ❌ Missing | HIGH |
| `/app/settings/attributes` | `/app/settings/attributes` | ❌ Missing | MEDIUM |
| `/app/settings/custom-roles` | `/app/settings/custom-roles` | ❌ Missing | MEDIUM |
| `/app/settings/assignment-policy` | `/app/settings/assignment-policy` | ❌ Missing | MEDIUM |
| `/app/settings/billing` | `/app/settings/billing` | ❌ Missing | MEDIUM |
| `/app/settings/security` | `/app/settings/security` | ❌ Missing | MEDIUM |
| `/app/settings/profile` | `/app/settings/profile` | ❌ Missing | MEDIUM |
| `/app/settings/integrations` | `/app/settings/integrations` | ❌ Missing | LOW |
| `/app/search` | `/app/search` | ⚠️ Partial | MEDIUM |
| `/app/reports` | `/app/reports` | ⚠️ Partial | MEDIUM |

---

## Appendix B: Component Inventory

### Components to Build (Estimated)

**Campaigns**: 15 components  
**Companies**: 10 components  
**Settings**: 40 components  
**Forms**: 20 components  
**Total**: ~85 new components

---

## Appendix C: Testing Strategy

### Test Types
1. **Unit Tests**: All stores, utilities, functions
2. **Component Tests**: All UI components
3. **Integration Tests**: API interactions
4. **E2E Tests**: Critical user flows
5. **Accessibility Tests**: WCAG 2.1 AA
6. **Performance Tests**: Lighthouse, Core Web Vitals

### Test Coverage Targets
- Stores: >85%
- Components: >75%
- API Clients: >85%
- Overall: >80%

---

## Conclusion

This specification provides a complete roadmap to achieve 100% parity with the Vue frontend. Following this plan will result in a production-ready Svelte SPA that can fully replace the Vue implementation.

**Key Outcomes**:
- ✅ 100% feature parity
- ✅ Complete route coverage
- ✅ Production-ready quality
- ✅ Full test coverage
- ✅ Ready for Vue deprecation

**Timeline**: 13-20 weeks  
**Effort**: 520-800 hours  
**Team**: 4-5 developers recommended

**Next Steps**:
1. Review and approve this specification
2. Prioritize Phase 8 features
3. Assign development team
4. Begin implementation
5. Track progress weekly
