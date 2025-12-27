<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';

  interface Props {
    inboxes?: Array<{ id: string; name: string; channelType: string }>;
    onSubmit?: (data: { inbox: string; contact: string; message: string }) => void;
    onCancel?: () => void;
    class?: string;
  }

  let { inboxes = [], onSubmit, onCancel, class: className }: Props = $props();
  
  let selectedInbox = $state('');
  let contact = $state('');
  let message = $state('');

  function handleSubmit() {
    onSubmit?.({ inbox: selectedInbox, contact, message });
  }
</script>

<form class={cn('space-y-4', className)} onsubmit|preventDefault={handleSubmit}>
  <div>
    <Label for="inbox">Inbox</Label>
    <select 
      id="inbox"
      bind:value={selectedInbox}
      class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"
    >
      <option value="">Select an inbox</option>
      {#each inboxes as inbox}
        <option value={inbox.id}>{inbox.name} ({inbox.channelType})</option>
      {/each}
    </select>
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
    <Button type="submit" disabled={!selectedInbox || !contact || !message}>
      Start Conversation
    </Button>
  </div>
</form>
