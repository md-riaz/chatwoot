<script lang="ts">
	import * as Card from '$lib/components/ui/card';
	import type { Snippet } from 'svelte';

	let {
		onclick,
		children,
		class: className = '',
		...restProps
	} = $props<{
		onclick: () => void;
		children?: Snippet;
		class?: string;
		[key: string]: any;
	}>();
</script>

<button
	type="button"
	{onclick}
	class="block w-full text-left transition-shadow hover:shadow-md focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring rounded-lg {className}"
	{...restProps}
>
	<Card.Root>
		{#if children}
			{@render children()}
		{/if}
	</Card.Root>
</button>
