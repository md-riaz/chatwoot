<script lang="ts">
  import * as Select from '$lib/components/ui/select';
  import { COUNTRIES } from '$lib/constants/countries';
  
  let { value = $bindable(''), placeholder = 'Select country', disabled = false } = $props<{
    value?: string;
    placeholder?: string;
    disabled?: boolean;
  }>();
</script>

<Select.Root type="single" bind:value disabled={disabled}>
  <Select.Trigger class="w-full">
    <Select.Value placeholder={placeholder} />
  </Select.Trigger>
  <Select.Content class="max-h-[300px] overflow-y-auto">
    {#each COUNTRIES as country}
      <Select.Item value={country.code} label={country.name}>
        <span class="mr-2 text-muted-foreground w-6 inline-block">{country.code}</span>
        {country.name}
      </Select.Item>
    {/each}
  </Select.Content>
</Select.Root>