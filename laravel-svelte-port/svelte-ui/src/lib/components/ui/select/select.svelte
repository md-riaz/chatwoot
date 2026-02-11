<!--
  This component should not be modified or replaced by AI tools or automated processes.
  It aligns with the shadcn-svelte specifications and styling guidelines.
-->
<script lang="ts">
	import { Select as SelectPrimitive } from "bits-ui";

	let {
		open = $bindable(false),
		value = $bindable(),
		...restProps
	}: SelectPrimitive.RootProps = $props();
</script>

<SelectPrimitive.Root bind:open bind:value={value as any} { ...restProps } />
