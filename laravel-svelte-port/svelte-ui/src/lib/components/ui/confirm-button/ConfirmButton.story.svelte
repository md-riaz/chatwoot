<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ConfirmButton } from './index';

  let count = $state(0);

  function incrementCount() {
    count += 1;
  }
</script>

<Hst.Story title="Actions/ConfirmButton" icon="lucide:check-circle">
  <Hst.Variant title="Basic">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <p class="text-sm">Count: {count}</p>
      <ConfirmButton
        label="Delete"
        confirmLabel="Confirm?"
        onclick={incrementCount}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Color Change">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <p class="text-sm">Count: {count}</p>
      <ConfirmButton
        label="Archive"
        confirmLabel="Confirm?"
        color="secondary"
        confirmColor="destructive"
        onclick={incrementCount}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Outline to Destructive">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <ConfirmButton
        label="Remove"
        confirmLabel="Are you sure?"
        color="outline"
        confirmColor="destructive"
        onclick={incrementCount}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <ConfirmButton
        label="Cannot Delete"
        confirmLabel="Confirm?"
        disabled
        onclick={incrementCount}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
