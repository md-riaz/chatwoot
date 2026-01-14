<script lang="ts">
  /**
   * LiveChatCampaignDialog
   * Dialog for creating/editing live chat campaigns
   */
  import { createEventDispatcher } from 'svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import LiveChatCampaignForm from './LiveChatCampaignForm.svelte';
  import type { Campaign, CreateCampaignParams } from '$lib/api/campaigns';

  interface Props {
    open: boolean;
    mode?: 'create' | 'edit';
    campaign?: Campaign | null;
  }

  let { open = $bindable(false), mode = 'create', campaign = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateCampaignParams;
    close: void;
  }>();

  function handleSubmit(data: CreateCampaignParams) {
    dispatch('submit', data);
    open = false;
  }

  function handleCancel() {
    dispatch('close');
    open = false;
  }
</script>

<Dialog.Root {open} onOpenChange={(value) => (open = value)}>
  <Dialog.Content class="max-w-2xl max-h-[85vh] overflow-y-auto">
    <Dialog.Header>
      <Dialog.Title>
        {mode === 'create' ? 'Create Live Chat Campaign' : 'Edit Live Chat Campaign'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Set up a new live chat campaign to engage with your website visitors.'
          : 'Update your live chat campaign settings.'}
      </Dialog.Description>
    </Dialog.Header>

    <LiveChatCampaignForm
      {mode}
      {campaign}
      on:submit={(e) => handleSubmit(e.detail)}
      on:cancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
