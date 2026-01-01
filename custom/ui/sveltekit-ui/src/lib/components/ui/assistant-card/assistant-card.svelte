<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';

  let {
    id = 0,
    name = '',
    description = '',
    updatedAt = '',
    createdAt = '',
    class: className = '',
    onclick = () => {},
    ...restProps
  }: {
    id?: number;
    name?: string;
    description?: string;
    updatedAt?: string;
    createdAt?: string;
    class?: string;
    onclick?: () => void;
  } = $props();

  function formatDate(dateString: string): string {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString();
  }
</script>

<div
  class={cn(
    'flex flex-col gap-3 p-4 border rounded-lg bg-card hover:bg-accent/50 cursor-pointer transition-colors',
    className
  )}
  onclick={onclick}
  role="button"
  tabindex="0"
  {...restProps}
>
  <div class="flex items-start justify-between">
    <div class="flex-1">
      <h3 class="font-medium text-lg">{name}</h3>
      {#if description}
        <p class="text-sm text-muted-foreground mt-1 line-clamp-2">
          {description}
        </p>
      {/if}
    </div>
    <Badge variant="secondary">AI Assistant</Badge>
  </div>

  <div class="flex items-center gap-4 text-xs text-muted-foreground">
    {#if updatedAt}
      <span>Updated: {formatDate(updatedAt)}</span>
    {:else if createdAt}
      <span>Created: {formatDate(createdAt)}</span>
    {/if}
  </div>
</div>
