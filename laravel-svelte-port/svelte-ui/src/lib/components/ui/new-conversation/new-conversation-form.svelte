<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import * as Select from '$lib/components/ui/select';

  interface Props {
    inboxes?: Array<{ id: string; name: string; channelType: string }>;
    onSubmit?: (data: { inbox: string; contact: string; message: string }) => void;
    onCancel?: () => void;
    class?: string;
  }

  let { inboxes = [], onSubmit, onCancel, class: className }: Props = $props();
  
  let selectedInbox = $state({ value: '' });
  let contact = $state('');
  let message = $state('');

  function handleSubmit(e: Event) {
    e.preventDefault();
    onSubmit?.({ inbox: selectedInbox.value, contact, message });
  }
</script>

<form class={cn('space-y-4', className)} onsubmit={handleSubmit}>
  <div class="space-y-2">
    <Label for="inbox">Inbox</Label>
    <Select.Root bind:value={selectedInbox}>
      <Select.Trigger id="inbox">
        <Select.Value placeholder="Select an inbox" />
      </Select.Trigger>
      <Select.Content>
        {#each inboxes as inbox}
          <Select.Item value={inbox.id}>{inbox.name} ({inbox.channelType})</Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
  </div>

  <div>
    <Label for="contact">Contact Email or Phone</Label>
    <Input id="contact" bind:value={contact} placeholder="email@example.com or +1234567890" />
  </div>

  <div>
    <Label for="message">Initial Message</Label>
    <Textarea id="message" bind:value={message} placeholder="Type your message..." rows={4} />
  </div>

  <div class="flex justify-end gap-2 pt-4">
    <Button type="button" variant="outline" onclick={onCancel}>Cancel</Button>
    <Button type="submit" disabled={!selectedInbox.value || !contact || !message}>
      Start Conversation
    </Button>
  </div>
</form>
