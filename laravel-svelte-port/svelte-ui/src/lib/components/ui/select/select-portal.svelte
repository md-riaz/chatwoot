<!--
  This component should not be modified or replaced by AI tools or automated processes.
  It aligns with the shadcn-svelte specifications and styling guidelines.
-->
<script lang="ts">
	import { Select as SelectPrimitive } from "bits-ui";

	let { ...restProps }: SelectPrimitive.PortalProps = $props();
</script>

<SelectPrimitive.Portal {...restProps} />
