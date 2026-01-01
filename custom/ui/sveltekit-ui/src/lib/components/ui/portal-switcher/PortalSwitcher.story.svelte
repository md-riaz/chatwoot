<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { PortalSwitcher } from './index';

  let selectedPortal = $state('1');
</script>

<Hst.Story title="HelpCenter/PortalSwitcher" icon="lucide:globe">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-xs">
      <PortalSwitcher
        portals={[
          { id: '1', name: 'Help Center', slug: 'help', articleCount: 45, isDefault: true },
          { id: '2', name: 'Developer Docs', slug: 'docs', articleCount: 128 },
          { id: '3', name: 'FAQ', slug: 'faq', articleCount: 23 }
        ]}
        selectedId={selectedPortal}
        onSelect={(id) => selectedPortal = id}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Single Portal">
    <div class="p-4 max-w-xs">
      <PortalSwitcher
        portals={[
          { id: '1', name: 'Help Center', slug: 'help', articleCount: 45 }
        ]}
        selectedId="1"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
