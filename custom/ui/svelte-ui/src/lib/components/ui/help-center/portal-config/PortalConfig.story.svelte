<script lang="ts">
  import { PortalConfig } from './index.js';
  export let Hst: any;

  const defaultConfig = {
    siteName: 'Chatwoot Help Center',
    description: 'Find answers to your questions and learn how to use Chatwoot',
    logo: 'https://via.placeholder.com/150x50',
    customDomain: 'help.chatwoot.com',
    primaryColor: '#3B82F6',
    fontFamily: 'Inter',
    seoTitle: 'Help Center | Chatwoot',
    seoDescription: 'Get help with Chatwoot - customer support software'
  };
</script>

<Hst.Story title="Help Center/Portal Configuration" icon="lucide:settings">
  <Hst.Variant title="Default Config">
    <PortalConfig
      config={{...defaultConfig}}
      onSave={(config) => console.log('Save:', config)}
    />
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <PortalConfig
      config={{
        siteName: '',
        description: '',
        logo: '',
        customDomain: '',
        primaryColor: '#3B82F6',
        fontFamily: 'Inter',
        seoTitle: '',
        seoDescription: ''
      }}
      onSave={(config) => console.log('Save:', config)}
    />
  </Hst.Variant>

  <Hst.Variant title="Custom Theme">
    <PortalConfig
      config={{
        ...defaultConfig,
        primaryColor: '#10B981',
        fontFamily: 'Roboto'
      }}
      onSave={(config) => console.log('Save:', config)}
    />
  </Hst.Variant>

  <Hst.Variant title="Saving State">
    <PortalConfig
      config={{...defaultConfig}}
      saving={true}
      onSave={(config) => console.log('Save:', config)}
    />
  </Hst.Variant>

  <Hst.Variant title="With Custom Domain">
    <PortalConfig
      config={{
        ...defaultConfig,
        customDomain: 'support.mycompany.com'
      }}
      onSave={(config) => console.log('Save:', config)}
    />
  </Hst.Variant>
</Hst.Story>
