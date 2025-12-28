<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Switch } from '$lib/components/ui/switch';
  import { Card } from '$lib/components/ui/card';

  export let locales: {
    code: string;
    name: string;
    enabled: boolean;
    isDefault: boolean;
    articleCount: number;
  }[] = [];

  export let onToggle: (code: string) => void = () => {};
  export let onSetDefault: (code: string) => void = () => {};

  function handleToggle(code: string) {
    onToggle(code);
  }

  function handleSetDefault(code: string) {
    if (confirm(`Set ${code.toUpperCase()} as the default locale?`)) {
      onSetDefault(code);
    }
  }
</script>

<div class="w-full max-w-4xl mx-auto space-y-6 p-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold">Locales</h2>
  </div>

  <div class="space-y-3">
    {#each locales as locale (locale.code)}
      <Card class="p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4 flex-1">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold">{locale.name}</h3>
                <Badge variant="secondary">{locale.code.toUpperCase()}</Badge>
                {#if locale.isDefault}
                  <Badge>Default</Badge>
                {/if}
              </div>
              <p class="text-sm text-muted-foreground mt-1">
                {locale.articleCount} article{locale.articleCount !== 1 ? 's' : ''}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            {#if !locale.isDefault}
              <Button variant="outline" size="sm" on:click={() => handleSetDefault(locale.code)}>
                Set as Default
              </Button>
            {/if}
            <Switch checked={locale.enabled} on:checkedChange={() => handleToggle(locale.code)} />
          </div>
        </div>
      </Card>
    {/each}
  </div>
</div>
