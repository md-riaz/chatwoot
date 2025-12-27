<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Toast } from './index';
  import { Button } from '$lib/components/ui/button';

  let showDefault = $state(false);
  let showSuccess = $state(false);
  let showDestructive = $state(false);
  let showWarning = $state(false);
</script>

<Hst.Story title="Feedback/Toast" icon="lucide:bell">
  <Hst.Variant title="All Variants">
    <div class="p-4 bg-background space-y-4">
      <div class="flex flex-wrap gap-2">
        <Button onclick={() => showDefault = true}>Show Default</Button>
        <Button onclick={() => showSuccess = true} variant="outline">Show Success</Button>
        <Button onclick={() => showDestructive = true} variant="destructive">Show Error</Button>
        <Button onclick={() => showWarning = true} variant="secondary">Show Warning</Button>
      </div>
      
      <p class="text-sm text-muted-foreground">
        Click a button to show the corresponding toast notification.
      </p>
    </div>

    <Toast
      bind:open={showDefault}
      title="Notification"
      description="This is a default notification message."
    />
    <Toast
      bind:open={showSuccess}
      variant="success"
      title="Success!"
      description="Your changes have been saved successfully."
    />
    <Toast
      bind:open={showDestructive}
      variant="destructive"
      title="Error"
      description="Something went wrong. Please try again."
    />
    <Toast
      bind:open={showWarning}
      variant="warning"
      title="Warning"
      description="Your session will expire in 5 minutes."
    />
  </Hst.Variant>

  <Hst.Variant title="Static Examples">
    <div class="p-4 bg-background space-y-4 relative min-h-[400px]">
      <div class="absolute bottom-4 right-4 space-y-2">
        <Toast open title="Default Toast" description="This is a default message." duration={0} />
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
