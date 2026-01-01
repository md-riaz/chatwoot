<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { LocaleCard } from './index';

  const locales = [
    { locale: 'en', name: 'English', articlesCount: 45, isDefault: true },
    { locale: 'de', name: 'German', articlesCount: 32 },
    { locale: 'fr', name: 'French', articlesCount: 28 },
    { locale: 'es', name: 'Spanish', articlesCount: 25 },
    { locale: 'pt-BR', name: 'Portuguese (Brazil)', articlesCount: 20 },
    { locale: 'ja', name: 'Japanese', articlesCount: 15 },
  ];
</script>

<Hst.Story title="HelpCenter/LocaleCard" icon="lucide:globe">
  <Hst.Variant title="Locale List">
    <div class="flex flex-col gap-4 p-4 bg-background max-w-md">
      {#each locales as locale}
        <LocaleCard
          locale={locale.locale}
          name={locale.name}
          articlesCount={locale.articlesCount}
          isDefault={locale.isDefault}
          onclick={() => console.log('Clicked locale:', locale.locale)}
        />
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Default Locale">
    <div class="p-4 bg-background max-w-md">
      <LocaleCard
        locale="en"
        name="English"
        articlesCount={45}
        isDefault
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Non-Default Locale">
    <div class="p-4 bg-background max-w-md">
      <LocaleCard
        locale="de"
        name="German"
        articlesCount={32}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
