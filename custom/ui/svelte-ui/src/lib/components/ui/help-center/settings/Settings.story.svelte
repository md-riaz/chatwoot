<script lang="ts">
  import { Settings } from './index.js';
  export let Hst: any;

  const defaultSettings = {
    isPublic: true,
    allowIndexing: true,
    showBranding: true,
    enableAnalytics: false,
    analyticsId: '',
    customCss: '',
    customJs: ''
  };
</script>

<Hst.Story title="Help Center/Settings" icon="lucide:sliders">
  <Hst.Variant title="Default Settings">
    <Settings
      settings={{...defaultSettings}}
      onSave={(settings) => console.log('Save:', settings)}
    />
  </Hst.Variant>

  <Hst.Variant title="With Analytics">
    <Settings
      settings={{
        ...defaultSettings,
        enableAnalytics: true,
        analyticsId: 'G-XXXXXXXXXX'
      }}
      onSave={(settings) => console.log('Save:', settings)}
    />
  </Hst.Variant>

  <Hst.Variant title="With Custom Code">
    <Settings
      settings={{
        ...defaultSettings,
        customCss: '.header { background: #333; }',
        customJs: 'console.log("Custom script loaded");'
      }}
      onSave={(settings) => console.log('Save:', settings)}
    />
  </Hst.Variant>

  <Hst.Variant title="Private Mode">
    <Settings
      settings={{
        ...defaultSettings,
        isPublic: false,
        allowIndexing: false,
        showBranding: false
      }}
      onSave={(settings) => console.log('Save:', settings)}
    />
  </Hst.Variant>
</Hst.Story>
