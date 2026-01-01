<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ContactNoteItem } from './index';
</script>

<Hst.Story title="Contacts/Note" icon="lucide:sticky-note">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-md">
      <ContactNoteItem
        content="Customer prefers email communication. Has been a client since 2020."
        author={{ name: 'John Agent' }}
        createdAt="2 hours ago"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Notes List">
    <div class="p-4 max-w-md space-y-3">
      <ContactNoteItem
        content="VIP customer - always prioritize their requests"
        author={{ name: 'Jane Manager' }}
        createdAt="Yesterday"
      />
      <ContactNoteItem
        content="Interested in enterprise plan upgrade. Follow up next week."
        author={{ name: 'Sales Team' }}
        createdAt="3 days ago"
      />
      <ContactNoteItem
        content="Had issues with billing last month. Resolved with credit."
        author={{ name: 'Support Agent' }}
        createdAt="Last week"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
