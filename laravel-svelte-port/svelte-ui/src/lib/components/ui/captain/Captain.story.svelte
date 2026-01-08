<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { AnimatingImg, AddNewRulesInput, InboxCard, ResponseCard, RuleCard, ScenariosCard, SuggestedRules, ToolsDropdown, SettingsHeader } from './index';
</script>

<Hst.Story title="Captain" icon="lucide:bot">
  <Hst.Variant title="Settings Header">
    <div class="p-4 bg-white dark:bg-slate-900">
      <SettingsHeader
        title="Captain AI"
        description="AI-powered customer support assistant"
        status="active"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Animating Image">
    <div class="p-4">
      <AnimatingImg
        src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=300"
        alt="AI Assistant"
        animate={true}
        class="w-48 h-48"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Add New Rules Input">
    <div class="p-4 max-w-md">
      <AddNewRulesInput placeholder="Type a new automation rule..." />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Inbox Cards">
    <div class="p-4 space-y-3 max-w-md">
      <InboxCard name="Website Chat" channelType="web" conversationCount={24} isEnabled={true} />
      <InboxCard name="Support Email" channelType="email" conversationCount={156} isEnabled={true} />
      <InboxCard name="WhatsApp Business" channelType="whatsapp" conversationCount={89} isEnabled={false} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Response Card">
    <div class="p-4 max-w-md">
      <ResponseCard
        title="Greeting Response"
        content="Hello! Welcome to our support. How can I help you today? I'm here to assist with any questions about our products or services."
        category="Greetings"
        isActive={true}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Rule Card">
    <div class="p-4 max-w-md">
      <RuleCard
        name="Auto-assign VIP customers"
        description="Automatically assign conversations from VIP customers to senior agents"
        conditions={['Customer is VIP', 'Channel is Email']}
        actions={['Assign to Senior Team', 'Set Priority High']}
        isEnabled={true}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Scenarios Card">
    <div class="p-4 max-w-md">
      <ScenariosCard
        title="Order Inquiries"
        description="Handle common order-related questions"
        scenarios={[
          { id: '1', trigger: 'User asks about order status', response: 'Check order in system and provide status' },
          { id: '2', trigger: 'User wants to cancel order', response: 'Verify order eligibility and process cancellation' }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Suggested Rules">
    <div class="p-4 max-w-md">
      <SuggestedRules
        rules={[
          { id: '1', name: 'Weekend Auto-Reply', description: 'Automatically reply to messages received during weekends', confidence: 92 },
          { id: '2', name: 'Spam Detection', description: 'Detect and flag potential spam messages', confidence: 85 }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Tools Dropdown">
    <div class="p-4">
      <ToolsDropdown />
    </div>
  </Hst.Variant>
</Hst.Story>
