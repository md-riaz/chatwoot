<script lang="ts">
  import { ContactForm } from './index.js';
  export let Hst: any;

  const sampleContact = {
    id: '1',
    name: 'Jane Doe',
    email: 'jane.doe@example.com',
    phone: '+1 (555) 987-6543',
    company: 'Example Co',
    tags: ['customer'],
    status: 'active',
  };
</script>

<Hst.Story title="Contact Management/Contact Form" icon="lucide:user">
  <Hst.Variant title="Create New">
    <div class="p-4">
      <ContactForm on:save={(e) => console.log('save', e.detail)} on:cancel={() => console.log('cancel')} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Edit Contact">
    <div class="p-4">
      <ContactForm contact={sampleContact} on:save={(e) => console.log('save', e.detail)} on:cancel={() => console.log('cancel')} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Validation Errors">
    <div class="p-4">
      <ContactForm contact={{ ...sampleContact, name: '', email: 'invalid' }} on:save={(e) => console.log('save', e.detail)} />
    </div>
  </Hst.Variant>
</Hst.Story>
