<script lang="ts">
  import { Card } from '$lib/components/ui/card';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import LoadingSkeleton from './LoadingSkeleton.svelte';
  import { Line } from 'svelte-chartjs';
  import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    CategoryScale,
    LinearScale,
    PointElement,
  } from 'chart.js';

  ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    CategoryScale,
    LinearScale,
    PointElement
  );

  interface Props {
    title: string;
    metricKey: string;
    groupBy?: any;
    isTime?: boolean;
  }

  let {
    title,
    metricKey,
    groupBy = null,
    isTime = false,
  }: Props = $props();

  const chartData = $derived(reportsStore.getChartData(metricKey));
  const isLoading = $derived(reportsStore.getUIFlag(`isFetching_${metricKey}`));

  const data = $derived({
    labels: chartData?.labels || [],
    datasets: [
      {
        label: title,
        data: chartData?.data || [],
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
      },
    ],
  });

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
      title: {
        display: false,
      },
      tooltip: {
        callbacks: {
          label: (context: any) => {
            let label = context.dataset.label || '';
            if (label) {
              label += ': ';
            }
            if (isTime) {
              label += formatTime(context.parsed.y);
            } else {
              label += context.parsed.y.toLocaleString();
            }
            return label;
          },
        },
      },
    },
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  };

  function formatTime(seconds: number): string {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    
    if (hours > 0) {
      return `${hours}h ${minutes}m`;
    } else if (minutes > 0) {
      return `${minutes}m ${secs}s`;
    } else {
      return `${secs}s`;
    }
  }
</script>

<Card class="p-6">
  <h3 class="text-lg font-semibold mb-4">{title}</h3>
  
  {#if isLoading}
    <div class="h-[300px] animate-pulse bg-muted rounded"></div>
  {:else if chartData && chartData.data.length > 0}
    <div class="h-[300px]">
      <Line {data} {options} />
    </div>
  {:else}
    <div class="h-[300px] flex items-center justify-center text-muted-foreground">
      No data available
    </div>
  {/if}
</Card>
