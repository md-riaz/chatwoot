<script lang="ts">
  /**
   * Widget Preview Body Component
   * Displays the message area of the chat widget preview
   */
  interface Props {
    isDefaultScreen?: boolean;
  }

  let { isDefaultScreen = true }: Props = $props();
</script>

<div
  class="flex-1 overflow-y-auto p-4 space-y-3 bg-white dark:bg-slate-950"
  class:hidden={isDefaultScreen}
>
  <!-- Sample messages -->
  <div class="flex items-start gap-2">
    <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
      A
    </div>
    <div class="flex-1">
      <div class="bg-slate-100 dark:bg-slate-800 rounded-lg p-3 text-sm">
        <p class="text-slate-900 dark:text-slate-100">Hi! How can I help you today?</p>
      </div>
      <span class="text-xs text-slate-500 dark:text-slate-400 mt-1 block">Agent • Just now</span>
    </div>
  </div>

  <div class="flex items-start gap-2 justify-end">
    <div class="flex-1 flex justify-end">
      <div class="bg-blue-500 rounded-lg p-3 text-sm max-w-[80%]">
        <p class="text-white">I have a question about my order</p>
      </div>
    </div>
  </div>
</div>
