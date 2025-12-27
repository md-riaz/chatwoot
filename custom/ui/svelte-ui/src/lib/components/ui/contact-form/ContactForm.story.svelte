<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ContactForm, ContactMergeForm } from './index';
</script>

<Hst.Story title="Contacts/Form" icon="lucide:user-plus">
  <Hst.Variant title="New Contact">
    <div class="p-4 max-w-lg bg-white dark:bg-slate-900 rounded-lg">
      <h3 class="text-lg font-semibold mb-4">New Contact</h3>
      <ContactForm />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Edit Contact">
    <div class="p-4 max-w-lg bg-white dark:bg-slate-900 rounded-lg">
      <h3 class="text-lg font-semibold mb-4">Edit Contact</h3>
      <ContactForm
        contact={{
          name: 'John Doe',
          email: 'john@example.com',
          phone: '+1 234 567 8900',
          company: 'Acme Inc.',
          location: 'San Francisco, CA',
          bio: 'Product Manager with 5 years of experience'
        }}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Merge Contacts">
    <div class="p-4 max-w-lg bg-white dark:bg-slate-900 rounded-lg">
      <h3 class="text-lg font-semibold mb-4">Merge Contacts</h3>
      <ContactMergeForm
        primaryContact={{
          id: '1',
          name: 'John Doe',
          email: 'john@example.com',
          conversationCount: 15
        }}
        duplicateContacts={[
          { id: '2', name: 'John D.', email: 'johnd@example.com', conversationCount: 3 },
          { id: '3', name: 'J. Doe', phone: '+1 234 567 8900', conversationCount: 7 }
        ]}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
