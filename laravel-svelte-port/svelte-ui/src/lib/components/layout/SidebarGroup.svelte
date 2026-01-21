<script lang="ts">
  import * as Sidebar from "$lib/components/ui/sidebar/index.js";
  import type { SidebarSection } from "./types";
  import SidebarMenuItem from "./SidebarMenuItem.svelte";

  interface Props {
    section: SidebarSection;
  }

  let { section }: Props = $props();
</script>

<Sidebar.Group>
  {#if section.title}
    <Sidebar.GroupLabel>{section.title}</Sidebar.GroupLabel>
  {/if}
  <Sidebar.GroupContent>
    <Sidebar.Menu>
      {#each section.items as item (item.id)}
        <SidebarMenuItem {item} />
      {/each}
    </Sidebar.Menu>
  </Sidebar.GroupContent>
</Sidebar.Group>
