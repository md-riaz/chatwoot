<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { InlineInput } from './index';
</script>

<Hst.Story title="Forms/InlineInput" icon="lucide:text-cursor-input">
  <Hst.Variant title="Default">
    <div class="p-4 bg-background">
      <InlineInput id="inline-input-1" placeholder="Default InlineInput" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Label">
    <div class="p-4 bg-background">
      <InlineInput
        id="inline-input-2"
        label="Username"
        placeholder="Enter your username"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled">
    <div class="p-4 bg-background">
      <InlineInput
        id="inline-input-3"
        label="Disabled InlineInput"
        placeholder="Can't type here"
        disabled
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Custom Classes">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <InlineInput
        id="inline-input-4"
        label="Custom Input Class"
        placeholder="Custom input style"
        customInputClass="placeholder:text-blue-500"
      />
      <InlineInput
        id="inline-input-5"
        label="Custom Label Class"
        placeholder="Custom label style"
        customLabelClass="text-blue-500"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Different Types">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <InlineInput id="inline-input-6" label="Text" placeholder="Text input" />
      <InlineInput
        id="inline-input-7"
        label="Number"
        placeholder="Number input"
        type="number"
      />
      <InlineInput
        id="inline-input-8"
        label="Password"
        placeholder="Password input"
        type="password"
      />
      <InlineInput
        id="inline-input-9"
        label="Email"
        placeholder="Email input"
        type="email"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
