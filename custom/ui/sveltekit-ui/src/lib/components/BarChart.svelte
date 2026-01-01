<script lang="ts">
	import { Chart, registerables } from 'chart.js';
	
	Chart.register(...registerables);
	
	interface ChartProps {
		data: Array<[string, number]>;
	}
	
	let { data = [] }: ChartProps = $props();
	let canvasRef: HTMLCanvasElement | undefined = $state();
	let chartInstance: Chart | undefined = $state();
	let containerRef: HTMLDivElement | undefined = $state();
	
	// Responsive chart update with automatic cleanup
	$effect(() => {
		if (!canvasRef || !data || data.length === 0) return;
		
		// Destroy existing chart
		if (chartInstance) {
			chartInstance.destroy();
		}
		
		const ctx = canvasRef.getContext('2d');
		if (!ctx) return;
		
		const labels = data.map(d => d[0]);
		const values = data.map(d => d[1]);
		
		// Create new chart with Chart.js
		chartInstance = new Chart(ctx, {
			type: 'bar',
			data: {
				labels,
				datasets: [{
					label: 'Count',
					data: values,
					backgroundColor: 'rgba(31, 147, 255, 0.8)', // Chatwoot blue matching Vue
					borderColor: 'rgb(31, 147, 255)',
					borderWidth: 1,
					borderRadius: 4,
					hoverBackgroundColor: 'rgba(31, 147, 255, 1)'
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						enabled: true,
						backgroundColor: 'rgba(15, 23, 42, 0.9)',
						titleColor: 'rgb(248, 250, 252)',
						bodyColor: 'rgb(226, 232, 240)',
						padding: 12,
						cornerRadius: 8,
						displayColors: false,
						callbacks: {
							title: (items) => items[0]?.label || '',
							label: (item) => `Count: ${item.formattedValue}`
						}
					}
				},
				scales: {
					x: {
						grid: {
							display: false
						},
						ticks: {
							color: 'rgb(148, 163, 184)', // slate-11
							font: {
								family: 'Inter',
								size: 12
							}
						}
					},
					y: {
						beginAtZero: true,
						grid: {
							color: 'rgba(148, 163, 184, 0.1)', // subtle grid lines
							drawBorder: false
						},
						ticks: {
							color: 'rgb(148, 163, 184)', // slate-11
							font: {
								family: 'Inter',
								size: 12
							},
							precision: 0
						}
					}
				},
				interaction: {
					intersect: false,
					mode: 'index'
				}
			}
		});
		
		// Cleanup function - automatically called when component unmounts or effect re-runs
		return () => {
			if (chartInstance) {
				chartInstance.destroy();
				chartInstance = undefined;
			}
		};
	});
</script>

<div bind:this={containerRef} class="relative w-full h-full" style="min-height: 300px; max-height: 400px;">
	<canvas
		bind:this={canvasRef}
		class="w-full h-full"
		role="img"
		aria-label="Bar chart showing conversation statistics"
	/>
</div>
