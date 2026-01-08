<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';

  interface FilterCondition {
    attributeKey: string;
    filterOperator: string;
    values: string[];
    queryOperator?: 'and' | 'or';
  }

  let {
    filters = [],
    class: className = '',
    onClear = () => {},
    ...restProps
  }: {
    filters: FilterCondition[];
    class?: string;
    onClear?: () => void;
  } = $props();
</script>

<div class={cn('flex items-center gap-2 flex-wrap', className)} {...restProps}>
  {#if filters.length > 0}
    <span class="text-sm text-muted-foreground">Filtered by:</span>
    
    {#each filters as filter, index}
      {#if index > 0 && filter.queryOperator}
        <span class="text-xs text-muted-foreground uppercase">
          {filter.queryOperator}
        </span>
      {/if}
      <Badge variant="secondary">
        {filter.attributeKey} {filter.filterOperator} {filter.values.join(', ')}
      </Badge>
    {/each}
    
    <Button variant="ghost" size="sm" onclick={onClear}>
      Clear all
    </Button>
  {:else}
    <span class="text-sm text-muted-foreground">No filters applied</span>
  {/if}
</div>
