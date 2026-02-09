<script lang="ts">
  import { Card } from '$lib/components/ui/card';
  import { TrendingUp, TrendingDown } from 'lucide-svelte';
  import { formatTime } from '$lib/utils/timeHelper';

  interface Props {
    label: string;
    value: number;
    trend?: number;
    isTime?: boolean;
    infoText?: string;
  }

  let {
    label,
    value,
    trend = 0,
    isTime = false,
    infoText = '',
  }: Props = $props();

  const formattedValue = $derived(
    isTime ? formatTime(value) : value.toLocaleString()
  );

  const trendColor = $derived(
    trend > 0 ? 'text-green-600' : trend < 0 ? 'text-red-600' : 'text-gray-600'
  );

  const TrendIcon = $derived(trend > 0 ? TrendingUp : TrendingDown);
</script>

<Card class="p-6">
  <div class="flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <span class="text-sm font-medium text-muted-foreground">{label}</span>
      {#if infoText}
        <span class="text-xs text-muted-foreground" title={infoText}>ℹ️</span>
      {/if}
    </div>
    
    <div class="flex items-baseline gap-2">
      <span class="text-3xl font-bold">{formattedValue}</span>
      
      {#if trend !== 0}
        <div class="flex items-center gap-1 {trendColor}">
          <svelte:component this={TrendIcon} class="h-4 w-4" />
          <span class="text-sm font-medium">{Math.abs(trend)}%</span>
        </div>
      {/if}
    </div>
  </div>
</Card>
