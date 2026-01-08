<script lang="ts">
  import { ContactDetails } from './index.js';
  export let Hst: any;

  const sample = {
    id: '1',
    name: 'Samuel Green',
    email: 'sam.green@example.com',
    phone: '+1 (555) 222-3333',
    company: 'Green Solutions',
    tags: ['customer', 'vip'],
    status: 'active',
    createdAt: '2024-01-10T10:00:00Z',
    lastActivity: '2024-01-27T12:00:00Z',
    timeline: [
      { id: 'a1', type: 'Email', text: 'Sent welcome email', date: '2024-01-15T12:00:00Z' },
      { id: 'a2', type: 'Conversation', text: 'Opened support conversation', date: '2024-01-20T09:30:00Z' },
      { id: 'a3', type: 'Note', text: 'Left voicemail', date: '2024-01-22T14:00:00Z' },
    ],
    notes: [
      { id: 'n1', author: 'Agent A', body: 'Reached out via email.', date: '2024-01-15T12:05:00Z' },
      { id: 'n2', author: 'Agent B', body: 'Left voicemail for follow-up.', date: '2024-01-22T14:10:00Z' },
    ],
  };
</script>

<Hst.Story title="Contact Management/Contact Details" icon="lucide:user">
  <Hst.Variant title="Default">
    <div class="p-4">
      <ContactDetails contact={sample} on:edit={(e) => console.log('edit', e.detail)} on:addNote={(e) => console.log('addNote', e.detail)} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="No Activity">
    <div class="p-4">
      <ContactDetails contact={{ ...sample, timeline: [], notes: [] }} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Edit Flow">
    <script>
      import { onMount } from 'svelte';
      let showEditor = false;
      function handleEdit() { showEditor = true; }
    </script>
    <div class="p-4">
      <ContactDetails contact={sample} on:edit={handleEdit} />
      {#if showEditor}
        <div class="fixed inset-0 z-40 flex items-center justify-center">
          <div class="absolute inset-0 bg-black/50"></div>
          <div class="relative w-full max-w-3xl bg-white p-4 rounded-lg z-50">
            <ContactDetails contact={sample} />
          </div>
        </div>
      {/if}
    </div>
  </Hst.Variant>
</Hst.Story>
