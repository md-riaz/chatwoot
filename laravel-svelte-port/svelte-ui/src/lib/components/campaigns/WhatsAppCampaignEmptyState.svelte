<script lang="ts">
  /**
   * WhatsAppCampaignEmptyState Component
   * Displays empty state for WhatsApp campaigns
   */
  import { MessageCircle } from 'lucide-svelte';

  interface Props {
    title?: string;
    subtitle?: string;
  }

  let {
    title = 'No WhatsApp Campaigns',
    subtitle = 'Create your first WhatsApp campaign to engage with your customers',
  }: Props = $props();
</script>

<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
  <div class="mb-4 rounded-full bg-green-100 dark:bg-green-900/20 p-4">
    <MessageCircle class="h-12 w-12 text-green-600 dark:text-green-500" />
  </div>
  
  <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2">
    {title}
  </h3>
  
  <p class="text-sm text-slate-600 dark:text-slate-400 max-w-md">
    {subtitle}
  </p>

  <div class="mt-8 flex flex-col gap-4 w-full max-w-2xl">
    <!-- Example Campaign Cards -->
    <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
      <div class="flex items-center gap-3 mb-2">
        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
        <span class="text-sm font-medium">Welcome Campaign</span>
      </div>
      <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
        Send a welcome message to new customers when they subscribe
      </p>
      <div class="mt-2 flex gap-2 text-xs text-slate-500">
        <span>📅 Scheduled</span>
        <span>•</span>
        <span>WhatsApp</span>
      </div>
    </div>

    <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
      <div class="flex items-center gap-3 mb-2">
        <div class="h-2 w-2 rounded-full bg-green-500"></div>
        <span class="text-sm font-medium">Product Update</span>
      </div>
      <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
        Notify customers about new features and product updates
      </p>
      <div class="mt-2 flex gap-2 text-xs text-slate-500">
        <span>✅ Active</span>
        <span>•</span>
        <span>WhatsApp</span>
      </div>
    </div>
  </div>
</div>
