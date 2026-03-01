<script lang="ts">
  import { ChevronRight } from 'lucide-svelte';
  import type { Snippet } from 'svelte';

  let {
    title,
    description = '',
    linkText = '',
    linkUrl = '',
    actions,
  }: {
    title: string;
    description?: string;
    linkText?: string;
    linkUrl?: string;
    actions?: Snippet;
  } = $props();
</script>

<div class="flex flex-col items-start w-full gap-2">
  <div class="flex items-center justify-between w-full gap-4">
    <h1 class="text-xl font-medium tracking-tight text-foreground">{title}</h1>
    {#if actions}
      <div class="hidden gap-2 sm:flex">
        {@render actions()}
      </div>
    {/if}
  </div>
  <div class="flex flex-col w-full gap-3 text-muted-foreground">
    {#if description}
      <p
        class="mb-0 text-sm font-normal line-clamp-5 sm:line-clamp-none max-w-3xl"
      >
        {description}
      </p>
    {/if}
    {#if linkUrl && linkText}
      <a
        href={linkUrl}
        target="_blank"
        rel="noopener noreferrer"
        class="items-center hidden gap-1 text-sm font-medium sm:inline-flex w-fit text-primary hover:underline"
      >
        {linkText}
        <ChevronRight class="flex-shrink-0 size-4" />
      </a>
    {/if}
  </div>
  {#if actions}
    <div
      class="flex flex-wrap items-start justify-start w-full gap-3 sm:hidden"
    >
      {@render actions()}
    </div>
  {/if}
</div>
