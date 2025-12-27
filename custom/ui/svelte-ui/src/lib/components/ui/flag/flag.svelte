<script lang="ts">
  import { cn } from '$lib/utils';

  let {
    country = 'us',
    squared = false,
    class: className = '',
    ...restProps
  }: {
    country?: string;
    squared?: boolean;
    class?: string;
  } = $props();

  function getFlagEmoji(countryCode: string): string {
    const codePoints = countryCode
      .toUpperCase()
      .split('')
      .map((char) => 127397 + char.charCodeAt(0));
    return String.fromCodePoint(...codePoints);
  }
</script>

<span
  class={cn(
    'inline-flex items-center justify-center text-lg',
    squared ? 'rounded-sm' : 'rounded-full',
    className
  )}
  role="img"
  aria-label={`Flag of ${country.toUpperCase()}`}
  {...restProps}
>
  {getFlagEmoji(country)}
</span>
