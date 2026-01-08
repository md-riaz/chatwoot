<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { CustomAttributes } from './index';

  const sampleAttributes = [
    {
      id: '1',
      attribute_key: 'company_name',
      attribute_display_name: 'Company Name',
      attribute_display_type: 'text' as const,
    },
    {
      id: '2',
      attribute_key: 'employee_count',
      attribute_display_name: 'Employee Count',
      attribute_display_type: 'number' as const,
    },
    {
      id: '3',
      attribute_key: 'subscription_date',
      attribute_display_name: 'Subscription Date',
      attribute_display_type: 'date' as const,
    },
    {
      id: '4',
      attribute_key: 'website',
      attribute_display_name: 'Website',
      attribute_display_type: 'link' as const,
    },
    {
      id: '5',
      attribute_key: 'verified',
      attribute_display_name: 'Verified Customer',
      attribute_display_type: 'checkbox' as const,
    },
  ];

  let attributeValues = $state({
    company_name: 'Acme Inc.',
    employee_count: 150,
    subscription_date: '2024-01-15',
    website: 'https://acme.com',
    verified: true,
  });

  let emptyValues = $state<Record<string, any>>({});
</script>

<Hst.Story title="Data/CustomAttributes" icon="lucide:list">
  <Hst.Variant title="With Values">
    <div class="p-4 bg-background max-w-md">
      <h3 class="text-sm font-medium mb-4">Contact Attributes</h3>
      <CustomAttributes
        attributes={sampleAttributes}
        bind:values={attributeValues}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty Form">
    <div class="p-4 bg-background max-w-md">
      <h3 class="text-sm font-medium mb-4">Contact Attributes</h3>
      <CustomAttributes
        attributes={sampleAttributes}
        bind:values={emptyValues}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Readonly">
    <div class="p-4 bg-background max-w-md">
      <h3 class="text-sm font-medium mb-4">Contact Attributes (Read Only)</h3>
      <CustomAttributes
        attributes={sampleAttributes}
        values={attributeValues}
        readonly
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="No Attributes">
    <div class="p-4 bg-background max-w-md">
      <h3 class="text-sm font-medium mb-4">Custom Attributes</h3>
      <CustomAttributes attributes={[]} />
    </div>
  </Hst.Variant>
</Hst.Story>
