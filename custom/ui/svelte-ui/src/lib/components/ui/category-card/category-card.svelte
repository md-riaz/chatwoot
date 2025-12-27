<script lang="ts">
  import { cn } from '$lib/utils';

  let {
    id = 0,
    name = '',
    slug = '',
    icon = '',
    description = '',
    articlesCount = 0,
    class: className = '',
    onclick = () => {},
    ...restProps
  }: {
    id?: number;
    name?: string;
    slug?: string;
    icon?: string;
    description?: string;
    articlesCount?: number;
    class?: string;
    onclick?: () => void;
  } = $props();
</script>

<div
  class={cn(
    'flex items-start gap-4 p-4 border rounded-lg bg-card hover:bg-accent/50 cursor-pointer transition-colors',
    className
  )}
  onclick={onclick}
  role="button"
  tabindex="0"
  {...restProps}
>
  {#if icon}
    <span class="text-3xl">{icon}</span>
  {:else}
    <div class="w-10 h-10 rounded bg-muted flex items-center justify-center text-lg">
      📁
    </div>
  {/if}

  <div class="flex-1 min-w-0">
    <h3 class="font-medium mb-1">{name}</h3>
    {#if description}
      <p class="text-sm text-muted-foreground line-clamp-2 mb-2">
        {description}
      </p>
    {/if}
    <span class="text-xs text-muted-foreground">
      {articlesCount} {articlesCount === 1 ? 'article' : 'articles'}
    </span>
  </div>
</div>
