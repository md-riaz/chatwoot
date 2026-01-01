<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { FeatureSpotlight } from './index';
</script>

<Hst.Story title="Feedback/FeatureSpotlight" icon="lucide:sparkles">
  <Hst.Variant title="New Feature">
    <div class="p-4 bg-background max-w-lg">
      <FeatureSpotlight
        title="Introducing AI-Powered Responses"
        description="Let Captain AI help you respond to customer queries faster with intelligent suggestions based on your conversation history."
        actionLabel="Try it now"
        isNew
        onAction={() => console.log('Action clicked')}
        onDismiss={() => console.log('Dismissed')}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Feature Update">
    <div class="p-4 bg-background max-w-lg">
      <FeatureSpotlight
        title="Enhanced Reporting Dashboard"
        description="We've added new metrics and visualization options to help you better understand your team's performance."
        actionLabel="View Dashboard"
        onAction={() => console.log('View clicked')}
        onDismiss={() => console.log('Dismissed')}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Upgrade Prompt">
    <div class="p-4 bg-background max-w-lg">
      <FeatureSpotlight
        title="Unlock Advanced Features"
        description="Upgrade to Pro to access automation workflows, custom integrations, and priority support."
        actionLabel="Upgrade Now"
        dismissLabel="Maybe Later"
        isNew
        onAction={() => console.log('Upgrade clicked')}
        onDismiss={() => console.log('Later clicked')}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
