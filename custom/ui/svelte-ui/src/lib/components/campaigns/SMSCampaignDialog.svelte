<script lang="ts">
  import { Dialog } from '$lib/components/ui/dialog';
  import SMSCampaignForm from './SMSCampaignForm.svelte';
  
  interface Props {
    open?: boolean;
    mode?: 'create' | 'edit';
    campaign?: any;
  }
  
  let {
    open = $bindable(false),
    mode = 'create',
    campaign = null
  }: Props = $props();
  
  let onsubmit: ((data: any) => void) | undefined = $state();
  
  function handleSubmit(data: any) {
    if (onsubmit) onsubmit(data);
    open = false;
  }
  
  function handleCancel() {
    open = false;
  }
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="max-w-2xl">
    <Dialog.Header>
      <Dialog.Title>
        {mode === 'create' ? 'Create SMS Campaign' : 'Edit SMS Campaign'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Create a new SMS campaign to send messages to your contacts.' 
          : 'Update your SMS campaign details.'}
      </Dialog.Description>
    </Dialog.Header>
    
    <SMSCampaignForm 
      {mode}
      {campaign}
      onsubmit={handleSubmit}
      oncancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
