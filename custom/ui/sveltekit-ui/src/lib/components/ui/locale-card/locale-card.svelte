<script lang="ts">
  import { cn } from '$lib/utils';
  import { Flag } from '$lib/components/ui/flag';
  import { Badge } from '$lib/components/ui/badge';

  let {
    locale = '',
    name = '',
    articlesCount = 0,
    isDefault = false,
    class: className = '',
    onclick = () => {},
    ...restProps
  }: {
    locale?: string;
    name?: string;
    articlesCount?: number;
    isDefault?: boolean;
    class?: string;
    onclick?: () => void;
  } = $props();

  // Map locale to country code for flag
  const localeToCountry: Record<string, string> = {
    en: 'us',
    'en-US': 'us',
    'en-GB': 'gb',
    de: 'de',
    fr: 'fr',
    es: 'es',
    pt: 'pt',
    'pt-BR': 'br',
    ja: 'jp',
    zh: 'cn',
    ko: 'kr',
    ar: 'sa',
    hi: 'in',
    it: 'it',
    nl: 'nl',
    ru: 'ru',
  };

  const countryCode = $derived(localeToCountry[locale] || locale.split('-')[0] || 'us');
</script>

<div
  class={cn(
    'flex items-center gap-4 p-4 border rounded-lg bg-card hover:bg-accent/50 cursor-pointer transition-colors',
    className
  )}
  onclick={onclick}
  role="button"
  tabindex="0"
  {...restProps}
>
  <Flag country={countryCode} class="text-2xl" />

  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2">
      <h3 class="font-medium">{name}</h3>
      {#if isDefault}
        <Badge variant="secondary" class="text-xs">Default</Badge>
      {/if}
    </div>
    <p class="text-sm text-muted-foreground">
      {locale} • {articlesCount} {articlesCount === 1 ? 'article' : 'articles'}
    </p>
  </div>
</div>
