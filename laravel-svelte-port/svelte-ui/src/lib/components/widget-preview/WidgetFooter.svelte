<script lang="ts">
  /**
   * Widget Preview Footer Component
   * Displays the message input area of the chat widget preview
   */
  import { Send } from 'lucide-svelte';

  interface Props {
    isDefaultScreen?: boolean;
  }

  let { isDefaultScreen = true }: Props = $props();
</script>

<div class="p-4 bg-white dark:bg-slate-950 rounded-b-lg border-t border-slate-200 dark:border-slate-800" class:hidden={isDefaultScreen}>
  <div class="flex items-center gap-2">
    <input
      type="text"
      placeholder="Type your message..."
      disabled
      class="flex-1 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
    />
    <button
      disabled
      class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50"
    >
      <Send class="h-4 w-4" />
    </button>
  </div>
  <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 text-center">
    Powered by Chatwoot
  </p>
</div>
