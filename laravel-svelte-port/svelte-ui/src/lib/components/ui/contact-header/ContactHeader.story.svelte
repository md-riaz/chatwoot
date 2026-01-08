<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ContactHeader } from './index';
</script>

<Hst.Story title="Contacts/Header" icon="lucide:user">
  <Hst.Variant title="Default">
    <div class="bg-white dark:bg-slate-900 rounded-lg">
      <ContactHeader
        name="John Doe"
        email="john@example.com"
        phone="+1 234 567 8900"
        status="online"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Labels">
    <div class="bg-white dark:bg-slate-900 rounded-lg">
      <ContactHeader
        name="Jane Smith"
        email="jane@company.com"
        status="away"
        labels={['VIP', 'Enterprise', 'Product Team']}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Offline">
    <div class="bg-white dark:bg-slate-900 rounded-lg">
      <ContactHeader
        name="Bob Wilson"
        email="bob@example.com"
        status="offline"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
