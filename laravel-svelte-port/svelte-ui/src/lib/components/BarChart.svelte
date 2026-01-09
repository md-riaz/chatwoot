<script lang="ts">
  import { Chart, Svg, Axis, Bars, Highlight, Tooltip } from 'layerchart';
  import { cn } from '$lib/utils';
  
  interface Props {
    data: any[];
    xKey?: string;
    yKey?: string;
    class?: string;
  }
  
  let {
    data = [],
    xKey = 'label',
    yKey = 'value',
    class: className
  }: Props = $props();
</script>

<div class={cn('w-full h-64', className)}>
  {#if data.length > 0}
    <Chart {data} x={xKey} y={yKey} padding={{ left: 16, bottom: 24 }}>
      <Svg>
        <Axis placement="left" grid rule />
        <Axis placement="bottom" rule />
        <Bars radius={4} strokeWidth={1} />
        <Highlight area />
      </Svg>
      <Tooltip header={(data) => data[xKey] || 'Unknown'}>
        {#snippet children(data)}
          <div class="tooltip">
            <div class="font-semibold">{data[xKey] || 'N/A'}</div>
            <div class="text-sm text-surface-content/50">{data[yKey] != null ? data[yKey] : 'N/A'}</div>
          </div>
        {/snippet}
      </Tooltip>
    </Chart>
  {:else}
    <div class="flex items-center justify-center h-full text-muted-foreground">
      No data available
    </div>
  {/if}
</div>

<style>
  .tooltip {
    @apply rounded-md border bg-popover p-2 text-popover-foreground shadow-md;
  }
</style>


