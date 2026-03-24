<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalsStore } from '$lib/stores/portals.svelte';
  import SectionLayout from '../../settings/account/components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import type { Portal } from '$lib/api/portals';

  const accountId = $derived(Number($page.params.accountId));
  const portals = $derived(portalsStore.allPortals);
  const isLoading = $derived(portalsStore.uiFlags.isFetching);

  onMount(() => {
    if (portalsStore.allPortals.length === 0) {
      portalsStore.fetchPortals();
    }
  });

  function getAllowedLocales(portal: Portal): string[] {
    const config = portal.config || {};
    const rawLocales =
      (config as Record<string, unknown>).allowedLocales ||
      (config as Record<string, unknown>).allowed_locales;

    if (!Array.isArray(rawLocales)) {
      return [];
    }

    return rawLocales
      .map(locale => {
        if (typeof locale === 'string') {
          return locale;
        }

        if (locale && typeof locale === 'object') {
          const maybeCode =
            (locale as Record<string, unknown>).code ||
            (locale as Record<string, unknown>).localeCode;
          return typeof maybeCode === 'string' ? maybeCode : '';
        }

        return '';
      })
      .filter(Boolean);
  }

  function getDefaultLocale(portal: Portal): string | null {
    const config = portal.config || {};
    const defaultLocale =
      (config as Record<string, unknown>).defaultLocale ||
      (config as Record<string, unknown>).default_locale;

    return typeof defaultLocale === 'string' ? defaultLocale : null;
  }

  function handleEditPortal(portal: Portal) {
    goto(`/app/accounts/${accountId}/portals/settings/${portal.slug}`);
  }
</script>

<SectionLayout
  title="Locales"
  description="Review the locales configured for each help center portal"
>
  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if portals.length === 0}
    <div class="p-4 text-center text-muted-foreground">
      No portals found. Create a portal before configuring locales.
    </div>
  {:else}
    <div class="grid gap-4">
      {#each portals as portal}
        <Card.Root>
          <Card.Header class="flex flex-row items-start justify-between gap-4">
            <div>
              <Card.Title>{portal.name}</Card.Title>
              <Card.Description>
                Portal slug: <span class="font-mono">{portal.slug}</span>
              </Card.Description>
            </div>
            <Button variant="outline" onclick={() => handleEditPortal(portal)}>
              Edit Portal
            </Button>
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="space-y-2">
              <div class="text-sm font-medium">Allowed Locales</div>
              {#if getAllowedLocales(portal).length > 0}
                <div class="flex flex-wrap gap-2">
                  {#each getAllowedLocales(portal) as locale}
                    <Badge variant="secondary">{locale}</Badge>
                  {/each}
                </div>
              {:else}
                <p class="text-sm text-muted-foreground">
                  No locales configured for this portal yet.
                </p>
              {/if}
            </div>

            <div class="space-y-2">
              <div class="text-sm font-medium">Default Locale</div>
              {#if getDefaultLocale(portal)}
                <Badge>{getDefaultLocale(portal)}</Badge>
              {:else}
                <p class="text-sm text-muted-foreground">
                  No default locale configured.
                </p>
              {/if}
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
</SectionLayout>
