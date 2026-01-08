<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type DetailItem = {
    label: string;
    value: string;
    icon?: Snippet;
  };

  type Props = {
    class?: string;
    items?: DetailItem[];
    children?: Snippet;
  };

  let { class: className, items = [], children, ...restProps }: Props = $props();
</script>

<div class={cn('mt-4 space-y-3', className)} {...restProps}>
  {#if children}
    {@render children()}
  {:else}
    {#each items as item}
      <div class="flex items-center gap-3 text-sm">
        {#if item.icon}
          <span class="text-muted-foreground shrink-0">
            {@render item.icon()}
          </span>
        {/if}
        <div class="flex-1 min-w-0">
          <span class="text-muted-foreground">{item.label}:</span>
          <span class="ml-2 truncate">{item.value}</span>
        </div>
      </div>
    {/each}
  {/if}
</div>
