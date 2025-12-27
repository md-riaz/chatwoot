<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { SidebarActionsHeader } from './index';
</script>

<Hst.Story title="Layout/SidebarActionsHeader" icon="lucide:panel-right">
  <Hst.Variant title="Default">
    <div class="bg-white dark:bg-slate-900 w-80">
      <SidebarActionsHeader
        title="Contact Details"
        subtitle="john@example.com"
        actions={[
          { id: 'edit', icon: '✏️', label: 'Edit' },
          { id: 'more', icon: '⋯', label: 'More' }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Close">
    <div class="bg-white dark:bg-slate-900 w-80">
      <SidebarActionsHeader
        title="Conversation Info"
        onClose={() => {}}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Title Only">
    <div class="bg-white dark:bg-slate-900 w-80">
      <SidebarActionsHeader title="Settings" />
    </div>
  </Hst.Variant>
</Hst.Story>
