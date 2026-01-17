<script lang="ts">
  /**
   * FilterChips - Horizontal filter chips with counts
   */
  
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import type { FilterChip } from './types';
  
  interface Props {
    filters: FilterChip[];
    onFilterChange?: (filterId: string) => void;
  }
  
  let { filters, onFilterChange }: Props = $props();
</script>

<div class="flex items-center gap-2 overflow-x-auto pb-2">
  {#each filters as filter}
    <Button
      variant={filter.isActive ? 'default' : 'outline-solid'}
      size="sm"
      class="gap-2 whitespace-nowrap"
      onclick={() => onFilterChange?.(filter.id)}
    >
      <span>{filter.label}</span>
      {#if filter.count > 0}
        <Badge 
          variant={filter.isActive ? 'secondary' : 'default'} 
          class="text-xs"
        >
          {filter.count > 99 ? '99+' : filter.count}
        </Badge>
      {/if}
    </Button>
  {/each}
</div>
