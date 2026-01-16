<script lang="ts">
  /**
   * ConversationEmpty - Empty state for conversations list
   */
  
  import { EmptyState } from '$lib/components/ui/empty-state';
  
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

<EmptyState
  title={message}
  {description}
  icon="💬"
  {actionLabel}
  {onAction}
/>
