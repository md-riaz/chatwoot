<script lang="ts">
  /**
   * ConversationEmpty - Empty state for conversations list
   */
  
  import { MessageSquare } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import * as EmptyState from '$lib/components/ui/empty-state';
  
  interface Props {
    message?: string;
    description?: string;
    actionLabel?: string;
    onAction?: () => void;
  }
  
  let {
    message = 'No conversations found',
    description = 'Start a new conversation or adjust your filters',
    actionLabel = 'Clear filters',
    onAction,
  }: Props = $props();
</script>

<EmptyState.Root>
  <EmptyState.Icon>
    <MessageSquare class="h-12 w-12 text-muted-foreground" />
  </EmptyState.Icon>
  <EmptyState.Title>{message}</EmptyState.Title>
  <EmptyState.Description>{description}</EmptyState.Description>
  {#if onAction && actionLabel}
    <EmptyState.Actions>
      <Button variant="outline" onclick={onAction}>
        {actionLabel}
      </Button>
    </EmptyState.Actions>
  {/if}
</EmptyState.Root>
