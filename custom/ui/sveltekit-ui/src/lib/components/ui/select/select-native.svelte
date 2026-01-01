<script lang="ts">
	import { cn } from '$lib/utils';

	let className: string | undefined = undefined;
	export { className as class };
	export let value: string = '';
	export let id: string | undefined = undefined;
</script>

<select
	{id}
	bind:value
	class={cn(
		'flex h-10 w-full rounded-md border border-slate-6 bg-white px-3 py-2 text-sm ring-offset-white',
		'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-iris-9 focus-visible:ring-offset-2',
		'disabled:cursor-not-allowed disabled:opacity-50',
		'dark:bg-slate-1 dark:ring-offset-slate-1',
		className
	)}
	{...$$restProps}
>
	<slot />
</select>
