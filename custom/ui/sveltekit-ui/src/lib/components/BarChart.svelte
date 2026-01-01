<script lang="ts">
	import { onMount } from 'svelte';
	
	interface ChartProps {
		data: Array<[string, number]>;
	}
	
	let { data = [] }: ChartProps = $props();
	let canvasRef: HTMLCanvasElement | undefined = $state();
	
	onMount(() => {
		if (!canvasRef || !data || data.length === 0) return;
		
		const ctx = canvasRef.getContext('2d');
		if (!ctx) return;
		
		// Simple bar chart implementation
		const labels = data.map(d => d[0]);
		const values = data.map(d => d[1]);
		const maxValue = Math.max(...values, 1);
		
		const width = canvasRef.width;
		const height = canvasRef.height;
		const barWidth = width / labels.length;
		const padding = 40;
		const chartHeight = height - padding * 2;
		
		// Clear canvas
		ctx.clearRect(0, 0, width, height);
		
		// Draw bars
		values.forEach((value, index) => {
			const barHeight = (value / maxValue) * chartHeight;
			const x = index * barWidth + barWidth * 0.1;
			const y = height - padding - barHeight;
			
			// Bar
			ctx.fillStyle = 'rgb(31, 147, 255)'; // Chatwoot blue matching Vue
			ctx.fillRect(x, y, barWidth * 0.8, barHeight);
			
			// Label
			ctx.fillStyle = 'rgb(var(--slate-11))';
			ctx.font = '12px Inter';
			ctx.textAlign = 'center';
			ctx.fillText(labels[index], x + barWidth * 0.4, height - padding + 20);
			
			// Value
			ctx.fillStyle = 'rgb(var(--slate-12))';
			ctx.font = '14px Inter';
			ctx.fillText(value.toString(), x + barWidth * 0.4, y - 5);
		});
	});
</script>

<canvas
	bind:this={canvasRef}
	width={800}
	height={400}
	class="w-full h-full"
	style="max-height: 400px;"
/>
