<script lang="ts">
  import { Check, ChevronsUpDown } from 'lucide-svelte';
  import * as Command from '$lib/components/ui/command';
  import * as Popover from '$lib/components/ui/popover';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { cn } from '$lib/utils';
  import { usePhonePicker } from '@kevwpl/svelte-o-phone';
  import { tick } from 'svelte';

  let {
    value = $bindable(''),
    country = $bindable('US'),
    placeholder = '',
    disabled = false,
    class: className = undefined
  } = $props<{
    value?: string;
    country?: string;
    placeholder?: string;
    disabled?: boolean;
    class?: string;
  }>();

  // We use a key to force re-initialization if the country changes externally and it's different from picker's state
  // or if we need to reset.
  // However, usePhonePicker is a rune-based hook, so it should be called at top level.
  
  const picker = usePhonePicker({
    initialCountry: country || 'US',
    initialValue: value || '',
    onchange: (_details: any) => {
      // details not used, relying on reactive getters
    }
  });

  // Sync picker selection back to country prop
  $effect(() => {
    if (picker.selectedCountry && picker.selectedCountry.code && picker.selectedCountry.code !== country) {
      country = picker.selectedCountry.code;
    }
  });

  // Sync picker input back to value prop
  $effect(() => {
    if (picker.input !== value) {
       value = picker.input;
    }
  });
  
  // Watch for external country changes
  $effect(() => {
    if (country && picker.selectedCountry && picker.selectedCountry.code !== country) {
      const found = picker.countryList.find((c: any) => c.code === country);
      if (found) {
        picker.selectCountry(found);
      }
    }
  });

  let open = $state(false);
  let search = $state('');

  function handleSelect(currentValue: string) {
    // cmdk/bits-ui often normalizes values to lowercase
    const selected = picker.countryList.find((c: any) => 
      c.name && c.name.toLowerCase() === currentValue.toLowerCase()
    );
    if (selected) {
      picker.selectCountry(selected);
    }
    open = false;
  }

  // Wrapper for onSelect that matches the expected signature
  function createSelectHandler(countryName: string) {
    return () => handleSelect(countryName);
  }
</script>

<div class={cn("flex gap-2", className)}>
  <Popover.Root bind:open>
    <Popover.Trigger>
      {#snippet child({ props })}
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          class="w-[80px] justify-between px-3"
          {...props}
          {disabled}
        >
          {#if picker.selectedCountry}
            <span class="text-lg">{picker.selectedCountry.flag}</span>
          {:else}
             Select
          {/if}
          <ChevronsUpDown class="ml-auto h-4 w-4 shrink-0 opacity-50" />
        </Button>
      {/snippet}
    </Popover.Trigger>
    <Popover.Content class="w-[300px] p-0" align="start">
      <Command.Root>
        <Command.Input placeholder="Search country..." bind:value={search} />
        <Command.List>
          <Command.Empty>No country found.</Command.Empty>
          <Command.Group>
            {#each picker.countryList as c (c.code)}
              {#if c.name}
                <Command.Item
                  value={c.name}
                  onSelect={createSelectHandler(c.name)}
                >
                  <Check
                    class={cn(
                      "mr-2 h-4 w-4",
                      picker.selectedCountry && picker.selectedCountry.code === c.code ? "opacity-100" : "opacity-0"
                    )}
                  />
                  <span class="mr-2 text-lg">{c.flag}</span>
                  {c.name}
                  <span class="ml-auto text-muted-foreground">{c.dialCode}</span>
                </Command.Item>
              {/if}
            {/each}
          </Command.Group>
        </Command.List>
      </Command.Root>
    </Popover.Content>
  </Popover.Root>

  <Input
    bind:ref={picker.ref}
    value={picker.input}
    oninput={picker.handleInput}
    {placeholder}
    {disabled}
    type="tel"
    class="flex-1"
  />
</div>
