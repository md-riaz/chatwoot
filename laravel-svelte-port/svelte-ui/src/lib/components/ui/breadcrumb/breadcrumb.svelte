<script lang="ts">
  import { cn } from '$lib/utils';

  interface BreadcrumbItem {
    label: string;
    link?: string;
    emoji?: string;
    count?: number;
  }

  let {
    items = [],
    countLabel = '',
    class: className = '',
    ...restProps
  }: {
    items: BreadcrumbItem[];
    countLabel?: string;
    class?: string;
  } = $props();
</script>

<nav aria-label="Breadcrumb" class={cn('flex items-center text-sm', className)} {...restProps}>
  <ol class="flex items-center gap-1.5">
    {#each items as item, index}
      <li class="flex items-center gap-1.5">
        {#if index > 0}
          <span class="text-muted-foreground">/</span>
        {/if}
        
        {#if item.emoji}
          <span>{item.emoji}</span>
        {/if}
        
        {#if item.link}
          <a
            href={item.link}
            class="text-muted-foreground hover:text-foreground transition-colors"
          >
            {item.label}
          </a>
        {:else}
          <span class="text-foreground font-medium">
            {item.label}
          </span>
        {/if}
        
        {#if item.count !== undefined && index === items.length - 1}
          <span class="text-muted-foreground">
            ({item.count} {countLabel})
          </span>
        {/if}
      </li>
    {/each}
  </ol>
</nav>
