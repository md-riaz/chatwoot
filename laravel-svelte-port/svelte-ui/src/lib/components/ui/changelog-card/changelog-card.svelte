<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';

  interface ChangelogEntry {
    id: string;
    title: string;
    description?: string;
    date: string;
    type: 'feature' | 'improvement' | 'fix' | 'breaking';
    version?: string;
  }

  let {
    entries = [],
    stacked = false,
    class: className = '',
    onclick = (_entry: ChangelogEntry) => {},
    ...restProps
  }: {
    entries?: ChangelogEntry[];
    stacked?: boolean;
    class?: string;
    onclick?: (entry: ChangelogEntry) => void;
  } = $props();

  const typeConfig: Record<ChangelogEntry['type'], { variant: 'default' | 'secondary' | 'destructive' | 'outline-solid'; label: string }> = {
    feature: { variant: 'default', label: 'New Feature' },
    improvement: { variant: 'secondary', label: 'Improvement' },
    fix: { variant: 'outline', label: 'Bug Fix' },
    breaking: { variant: 'destructive', label: 'Breaking' },
  };

  function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  }
</script>

<div class={cn('space-y-4', className)} {...restProps}>
  {#if stacked}
    <!-- Stacked grouped view -->
    <div class="border rounded-lg overflow-hidden">
      {#each entries as entry, index}
        <div
          class={cn(
            'p-4 cursor-pointer hover:bg-accent/50 transition-colors',
            index > 0 && 'border-t'
          )}
          onclick={() => onclick(entry)}
          onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && onclick(entry)}
          role="button"
          tabindex="0"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <Badge variant={typeConfig[entry.type].variant} class="text-xs">
                  {typeConfig[entry.type].label}
                </Badge>
                {#if entry.version}
                  <span class="text-xs text-muted-foreground">v{entry.version}</span>
                {/if}
              </div>
              <h3 class="font-medium">{entry.title}</h3>
              {#if entry.description}
                <p class="text-sm text-muted-foreground mt-1 line-clamp-2">
                  {entry.description}
                </p>
              {/if}
            </div>
            <span class="text-xs text-muted-foreground whitespace-nowrap">
              {formatDate(entry.date)}
            </span>
          </div>
        </div>
      {/each}
    </div>
  {:else}
    <!-- Individual cards -->
    {#each entries as entry}
      <div
        class="p-4 border rounded-lg cursor-pointer hover:bg-accent/50 transition-colors"
        onclick={() => onclick(entry)}
        onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && onclick(entry)}
        role="button"
        tabindex="0"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <Badge variant={typeConfig[entry.type].variant} class="text-xs">
                {typeConfig[entry.type].label}
              </Badge>
              {#if entry.version}
                <span class="text-xs text-muted-foreground">v{entry.version}</span>
              {/if}
            </div>
            <h3 class="font-medium">{entry.title}</h3>
            {#if entry.description}
              <p class="text-sm text-muted-foreground mt-1">
                {entry.description}
              </p>
            {/if}
          </div>
          <span class="text-xs text-muted-foreground whitespace-nowrap">
            {formatDate(entry.date)}
          </span>
        </div>
      </div>
    {/each}
  {/if}
</div>
