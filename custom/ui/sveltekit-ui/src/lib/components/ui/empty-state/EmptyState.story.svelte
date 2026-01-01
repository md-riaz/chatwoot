<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { EmptyState, ContactEmptyState, ArticleEmptyState, PortalEmptyState } from './index';
</script>

<Hst.Story title="EmptyState" icon="lucide:inbox">
  <Hst.Variant title="Generic">
    <div class="p-4 bg-white dark:bg-slate-900 rounded-lg">
      <EmptyState
        title="No messages"
        description="You don't have any messages yet. Start a conversation to see them here."
        icon="💬"
        actionLabel="Start Conversation"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Contact Empty State">
    <div class="p-4 bg-white dark:bg-slate-900 rounded-lg">
      <ContactEmptyState />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Article Empty State">
    <div class="p-4 bg-white dark:bg-slate-900 rounded-lg">
      <ArticleEmptyState />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Portal Empty State">
    <div class="p-4 bg-white dark:bg-slate-900 rounded-lg">
      <PortalEmptyState />
    </div>
  </Hst.Variant>

  <Hst.Variant title="No Search Results">
    <div class="p-4 bg-white dark:bg-slate-900 rounded-lg">
      <EmptyState
        title="No results found"
        description="We couldn't find anything matching your search. Try different keywords or filters."
        icon="🔍"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
