<script lang="ts">
  import EmptyState from './empty-state.svelte';

  interface Props {
    onCreateArticle?: () => void;
    class?: string;
  }

  let { onCreateArticle, class: className }: Props = $props();
</script>

<EmptyState
  title="No articles yet"
  description="Create your first article to start building your knowledge base and help your customers find answers."
  icon="📝"
  actionLabel="Create Article"
  onAction={onCreateArticle}
  class={className}
/>
