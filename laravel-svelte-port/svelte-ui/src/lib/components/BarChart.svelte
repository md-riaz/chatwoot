<script lang="ts">
  import { Chart, Svg, Axis, Bars, Highlight, Tooltip as LayerTooltip } from 'layerchart';
  import { cn } from '$lib/utils';
  import { _ } from '$lib/i18n';
  
  interface Props {
    data: any[];
    xKey?: string;
    yKey?: string;
    class?: string;
  }
  
  const Tooltip: any = LayerTooltip;

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
      <Tooltip header={(data: any) => data[xKey] || $_('common.unknown')}>
        {#snippet children(data: any)}
          <div class="rounded-md border bg-popover p-2 text-popover-foreground shadow-md">
            <div class="font-semibold">{data[xKey] || $_('common.not_available')}</div>
            <div class="text-sm text-surface-content/50">
              {data[yKey] != null ? data[yKey] : $_('common.not_available')}
            </div>
          </div>
        {/snippet}
      </Tooltip>
    </Chart>
  {:else}
    <div class="flex items-center justify-center h-full text-muted-foreground">
      {$_('common.empty.no_data_available')}
    </div>
  {/if}
</div>


