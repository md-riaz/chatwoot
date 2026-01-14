<script lang="ts">
	import { cn } from '$lib/utils';

	let {
		value = $bindable('#000000'),
		class: className,
		...restProps
	} = $props<{
		value?: string;
		class?: string;
		disabled?: boolean;
		name?: string;
	}>();
</script>

<input
	type="color"
	bind:value
	class={cn(
		'border-input bg-background h-10 w-full rounded-md border px-1 py-1 disabled:cursor-not-allowed disabled:opacity-50',
		className
	)}
	{...restProps}
/>
