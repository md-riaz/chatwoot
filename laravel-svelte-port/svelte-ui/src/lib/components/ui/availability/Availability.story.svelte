<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { AvailabilityText } from './index';
</script>

<Hst.Story title="Widget/Availability" icon="lucide:clock">
  <Hst.Variant title="Online">
    <div class="p-4 bg-white dark:bg-slate-800 rounded-lg max-w-sm">
      <AvailabilityText status="online" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Busy">
    <div class="p-4 bg-white dark:bg-slate-800 rounded-lg max-w-sm">
      <AvailabilityText status="busy" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Offline">
    <div class="p-4 bg-white dark:bg-slate-800 rounded-lg max-w-sm">
      <AvailabilityText status="offline" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Text">
    <div class="p-4 bg-white dark:bg-slate-800 rounded-lg max-w-sm">
      <AvailabilityText status="online" customText="Support team is available" />
    </div>
  </Hst.Variant>
</Hst.Story>
