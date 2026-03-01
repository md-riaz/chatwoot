<script lang="ts">
  import * as Card from '$lib/components/ui/card';
  import { ChevronRight } from 'lucide-svelte';
  import type { Snippet } from 'svelte';

  let {
    title,
    description,
    featureLabels = [],
    featureSnippet,
    onclick,
  }: {
    title: string;
    description: string;
    featureLabels?: string[];
    featureSnippet?: Snippet;
    onclick?: () => void;
  } = $props();
</script>

<!-- svelte-ignore a11y_click_events_have_key_events -->
<!-- svelte-ignore a11y_no_static_element_interactions -->
<div class="cursor-pointer group" {onclick}>
  <Card.Root
    class="transition-all duration-200 hover:shadow-md hover:border-primary/30"
  >
    <Card.Header class="pb-2">
      <div class="flex justify-between items-center">
        <Card.Title class="text-base font-medium">{title}</Card.Title>
        <ChevronRight
          class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors"
        />
      </div>
      <Card.Description class="text-sm">{description}</Card.Description>
    </Card.Header>
    <Card.Content>
      {#if featureSnippet}
        {@render featureSnippet()}
      {:else if featureLabels.length > 0}
        <ul class="flex flex-col gap-3">
          {#each featureLabels as label}
            <li class="flex items-center gap-2 text-sm text-muted-foreground">
              <span class="h-1.5 w-1.5 rounded-full bg-primary/60 flex-shrink-0"
              ></span>
              <span>{label}</span>
            </li>
          {/each}
        </ul>
      {/if}
    </Card.Content>
  </Card.Root>
</div>
