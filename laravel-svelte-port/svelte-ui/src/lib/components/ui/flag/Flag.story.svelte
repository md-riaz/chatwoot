<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Flag } from './index';

  const countries = [
    { id: 'us', name: 'United States' },
    { id: 'gb', name: 'United Kingdom' },
    { id: 'de', name: 'Germany' },
    { id: 'fr', name: 'France' },
    { id: 'in', name: 'India' },
    { id: 'jp', name: 'Japan' },
    { id: 'au', name: 'Australia' },
    { id: 'ca', name: 'Canada' },
    { id: 'br', name: 'Brazil' },
    { id: 'mx', name: 'Mexico' },
    { id: 'es', name: 'Spain' },
    { id: 'it', name: 'Italy' },
    { id: 'nl', name: 'Netherlands' },
    { id: 'se', name: 'Sweden' },
    { id: 'no', name: 'Norway' },
    { id: 'dk', name: 'Denmark' },
  ];

  let isSquared = $state(false);
</script>

<Hst.Story title="Visual/Flag" icon="lucide:flag">
  <Hst.Variant title="Basic Usage">
    <div class="flex items-center gap-4 p-4 border rounded bg-background">
      <Flag country="us" />
      <Flag country="gb" />
      <Flag country="de" />
      <Flag country="fr" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Size Variants">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" bind:checked={isSquared} />
        Squared flags
      </label>
      <div class="flex items-center gap-4 p-4 border rounded">
        <Flag country="in" class="text-sm" squared={isSquared} />
        <Flag country="in" class="text-lg" squared={isSquared} />
        <Flag country="in" class="text-2xl" squared={isSquared} />
        <Flag country="in" class="text-4xl" squared={isSquared} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="All Flags">
    <div class="flex flex-col gap-4 p-4 bg-background">
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" bind:checked={isSquared} />
        Squared flags
      </label>

      <div class="grid grid-cols-2 gap-4 p-4 border rounded md:grid-cols-3 lg:grid-cols-4">
        {#each countries as country}
          <div class="flex items-center gap-2 px-4 py-2 border rounded">
            <Flag country={country.id} squared={isSquared} class="text-xl" />
            <span class="text-sm">{country.name}</span>
          </div>
        {/each}
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
