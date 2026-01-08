<script lang="ts">
  import EmptyState from './empty-state.svelte';

  interface Props {
    onCreatePortal?: () => void;
    class?: string;
  }

  let { onCreatePortal, class: className }: Props = $props();
</script>

<EmptyState
  title="No portal configured"
  description="Create a help center portal to provide self-service support to your customers with articles and FAQs."
  icon="🌐"
  actionLabel="Create Portal"
  onAction={onCreatePortal}
  class={className}
/>
