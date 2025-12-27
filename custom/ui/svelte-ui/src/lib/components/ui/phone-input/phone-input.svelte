<script lang="ts">
  import { cn } from '$lib/utils';
  import { Input } from '$lib/components/ui/input';
  import { Button } from '$lib/components/ui/button';

  interface Country {
    id: string;
    name: string;
    dialCode: string;
  }

  const countries: Country[] = [
    { id: 'us', name: 'United States', dialCode: '+1' },
    { id: 'gb', name: 'United Kingdom', dialCode: '+44' },
    { id: 'de', name: 'Germany', dialCode: '+49' },
    { id: 'fr', name: 'France', dialCode: '+33' },
    { id: 'in', name: 'India', dialCode: '+91' },
    { id: 'jp', name: 'Japan', dialCode: '+81' },
    { id: 'au', name: 'Australia', dialCode: '+61' },
    { id: 'ca', name: 'Canada', dialCode: '+1' },
    { id: 'br', name: 'Brazil', dialCode: '+55' },
    { id: 'mx', name: 'Mexico', dialCode: '+52' },
  ];

  let {
    value = $bindable(''),
    placeholder = 'Enter phone number',
    disabled = false,
    showBorder = true,
    class: className = '',
    ...restProps
  }: {
    value?: string;
    placeholder?: string;
    disabled?: boolean;
    showBorder?: boolean;
    class?: string;
  } = $props();

  let selectedCountry = $state(countries[0]);
  let isOpen = $state(false);
  let phoneNumber = $state('');

  // Parse initial value
  $effect(() => {
    if (value) {
      const country = countries.find((c) => value.startsWith(c.dialCode));
      if (country) {
        selectedCountry = country;
        phoneNumber = value.replace(country.dialCode, '').trim();
      } else {
        phoneNumber = value;
      }
    }
  });

  function updateValue() {
    value = `${selectedCountry.dialCode}${phoneNumber}`;
  }

  function selectCountry(country: Country) {
    selectedCountry = country;
    isOpen = false;
    updateValue();
  }

  function getFlagEmoji(countryCode: string): string {
    const codePoints = countryCode
      .toUpperCase()
      .split('')
      .map((char) => 127397 + char.charCodeAt(0));
    return String.fromCodePoint(...codePoints);
  }
</script>

<div class={cn('relative flex items-center gap-2', className)} {...restProps}>
  <div class="relative">
    <Button
      variant="outline"
      size="sm"
      class={cn(
        'flex items-center gap-1 min-w-[80px]',
        !showBorder && 'border-0'
      )}
      {disabled}
      onclick={() => (isOpen = !isOpen)}
    >
      <span class="text-lg">{getFlagEmoji(selectedCountry.id)}</span>
      <span class="text-xs">{selectedCountry.dialCode}</span>
    </Button>

    {#if isOpen && !disabled}
      <div class="absolute z-50 top-full left-0 mt-1 bg-popover border rounded-md shadow-lg max-h-48 overflow-auto w-48">
        {#each countries as country}
          <button
            type="button"
            class="w-full px-3 py-2 text-left text-sm hover:bg-accent flex items-center gap-2"
            onclick={() => selectCountry(country)}
          >
            <span>{getFlagEmoji(country.id)}</span>
            <span class="flex-1">{country.name}</span>
            <span class="text-muted-foreground text-xs">{country.dialCode}</span>
          </button>
        {/each}
      </div>
    {/if}
  </div>

  <Input
    type="tel"
    bind:value={phoneNumber}
    {placeholder}
    {disabled}
    class={cn('flex-1', !showBorder && 'border-0')}
    oninput={updateValue}
  />
</div>
