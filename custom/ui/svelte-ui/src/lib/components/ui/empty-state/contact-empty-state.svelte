<script lang="ts">
  import EmptyState from './empty-state.svelte';

  interface Props {
    onAddContact?: () => void;
    class?: string;
  }

  let { onAddContact, class: className }: Props = $props();
</script>

<EmptyState
  title="No contacts yet"
  description="Add your first contact to start building your customer database and manage relationships."
  icon="👤"
  actionLabel="Add Contact"
  onAction={onAddContact}
  class={className}
/>
