<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { NewConversationForm } from './index';
</script>

<Hst.Story title="Conversation/NewForm" icon="lucide:message-square-plus">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-md bg-white dark:bg-slate-900 rounded-lg">
      <h3 class="text-lg font-semibold mb-4">New Conversation</h3>
      <NewConversationForm
        inboxes={[
          { id: '1', name: 'Website Chat', channelType: 'web' },
          { id: '2', name: 'Email Support', channelType: 'email' },
          { id: '3', name: 'WhatsApp', channelType: 'whatsapp' }
        ]}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
