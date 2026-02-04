<script lang="ts">
  import * as Select from '$lib/components/ui/select/index.js';
  import { COUNTRIES } from '$lib/constants/countries';
  
  let { value = $bindable(''), placeholder = 'Select country', disabled = false } = $props<{
    value?: string;
    placeholder?: string;
    disabled?: boolean;
  }>();

  // Derived trigger content - shows selected country name or placeholder
  const triggerContent = $derived(
    COUNTRIES.find((c) => c.code === value)?.name ?? placeholder
  );
</script>

<Select.Root type="single" bind:value {disabled}>
  <Select.Trigger class="w-full">
    {triggerContent}
  </Select.Trigger>
  <Select.Content class="max-h-[300px] overflow-y-auto">
    <Select.Group>
      {#each COUNTRIES as country (country.code)}
        <Select.Item value={country.code} label={country.name}>
          <span class="mr-2 text-muted-foreground w-6 inline-block">{country.code}</span>
          {country.name}
        </Select.Item>
      {/each}
    </Select.Group>
  </Select.Content>
</Select.Root>