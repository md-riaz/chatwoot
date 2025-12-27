<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { PhoneInput } from './index';

  let phoneNumber = $state('+14155552671');
  let emptyPhone = $state('');
</script>

<Hst.Story title="Forms/PhoneInput" icon="lucide:phone">
  <Hst.Variant title="Default">
    <div class="flex flex-col gap-4 p-4 h-[300px] bg-background">
      <PhoneInput bind:value={phoneNumber} placeholder="Enter phone number" />
      <div class="text-sm text-muted-foreground">Phone number value: {phoneNumber}</div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled">
    <div class="flex flex-col gap-4 p-4 h-[300px] bg-background">
      <PhoneInput
        value="+14155552671"
        placeholder="Enter phone number"
        disabled
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Without Border">
    <div class="flex flex-col gap-4 p-4 h-[300px] bg-background">
      <PhoneInput
        bind:value={phoneNumber}
        placeholder="Enter phone number"
        showBorder={false}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <div class="flex flex-col gap-4 p-4 h-[300px] bg-background">
      <PhoneInput bind:value={emptyPhone} placeholder="Enter phone number" />
      <div class="text-sm text-muted-foreground">Phone: {emptyPhone || '(empty)'}</div>
    </div>
  </Hst.Variant>
</Hst.Story>
