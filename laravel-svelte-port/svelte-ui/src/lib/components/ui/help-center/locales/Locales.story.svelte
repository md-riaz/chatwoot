<script lang="ts">
  import { Locales } from './index.js';
  export let Hst: any;

  const sampleLocales = [
    { code: 'en', name: 'English', enabled: true, isDefault: true, articleCount: 124 },
    { code: 'es', name: 'Spanish', enabled: true, isDefault: false, articleCount: 89 },
    { code: 'fr', name: 'French', enabled: true, isDefault: false, articleCount: 67 },
    { code: 'de', name: 'German', enabled: false, isDefault: false, articleCount: 34 },
    { code: 'pt', name: 'Portuguese', enabled: false, isDefault: false, articleCount: 12 }
  ];
</script>

<Hst.Story title="Help Center/Locales" icon="lucide:globe">
  <Hst.Variant title="All Locales">
    <Locales
      locales={sampleLocales}
      onToggle={(code) => console.log('Toggle:', code)}
      onSetDefault={(code) => console.log('Set Default:', code)}
    />
  </Hst.Variant>

  <Hst.Variant title="Few Locales">
    <Locales
      locales={sampleLocales.slice(0, 3)}
      onToggle={(code) => console.log('Toggle:', code)}
      onSetDefault={(code) => console.log('Set Default:', code)}
    />
  </Hst.Variant>

  <Hst.Variant title="Single Locale">
    <Locales
      locales={[sampleLocales[0]]}
      onToggle={(code) => console.log('Toggle:', code)}
      onSetDefault={(code) => console.log('Set Default:', code)}
    />
  </Hst.Variant>

  <Hst.Variant title="Mixed States">
    <Locales
      locales={sampleLocales}
      onToggle={(code) => console.log('Toggle:', code)}
      onSetDefault={(code) => console.log('Set Default:', code)}
    />
  </Hst.Variant>
</Hst.Story>
