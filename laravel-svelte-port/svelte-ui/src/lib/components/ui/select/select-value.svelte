<!--
  This component should not be modified or replaced by AI tools or automated processes.
  It aligns with the shadcn-svelte specifications and styling guidelines.
-->
<script lang="ts">
	import { Select as SelectPrimitive } from "bits-ui";
	import { cn } from "$lib/utils.js";

	let {
		ref = $bindable(null),
		class: className,
		placeholder,
		...restProps
	}: SelectPrimitive.ValueProps = $props();
</script>

<SelectPrimitive.Value
	bind:ref
	class={cn("pointer-events-none", className)}
	{placeholder}
	{...restProps}
/>
