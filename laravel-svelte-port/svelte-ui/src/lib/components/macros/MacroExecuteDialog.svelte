<script lang="ts">
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import type { Macro } from '$lib/api/macros';

  interface Props {
    open: boolean;
    macro?: Macro | null;
    isSubmitting?: boolean;
    onExecute?: (conversationIds: number[]) => void;
    onClose?: () => void;
  }

  let {
    open = $bindable(false),
    macro = null,
    isSubmitting = false,
    onExecute,
    onClose,
  }: Props = $props();

  let conversationIdsInput = $state('');
  let error = $state('');

  $effect(() => {
    if (open) {
      conversationIdsInput = '';
      error = '';
    }
  });

  function handleExecute() {
    const ids = conversationIdsInput
      .split(',')
      .map(item => Number(item.trim()))
      .filter(
        item => Number.isFinite(item) && Number.isInteger(item) && item > 0
      );

    if (!ids.length) {
      error = 'Enter at least one valid conversation ID.';
      return;
    }

    error = '';
    onExecute?.(ids);
  }
</script>

<Dialog.Root
  {open}
  onOpenChange={value => {
    open = value;
    if (!value) {
      onClose?.();
    }
  }}
>
  <Dialog.Content class="max-w-lg">
    <Dialog.Header>
      <Dialog.Title>Execute Macro</Dialog.Title>
      <Dialog.Description>
        {#if macro}
          Run <span class="font-medium">{macro.name}</span> against specific conversations.
        {:else}
          Run selected macro against specific conversations.
        {/if}
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-2">
      <Label for="conversation-ids">Conversation IDs (comma-separated)</Label>
      <Input
        id="conversation-ids"
        bind:value={conversationIdsInput}
        placeholder="12, 25, 40"
        disabled={isSubmitting}
      />
      {#if error}
        <p class="text-sm text-destructive">{error}</p>
      {/if}
    </div>

    <Dialog.Footer>
      <Button
        variant="outline"
        onclick={() => onClose?.()}
        disabled={isSubmitting}>Cancel</Button
      >
      <Button onclick={handleExecute} disabled={isSubmitting}
        >{isSubmitting ? 'Executing...' : 'Run Macro'}</Button
      >
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
