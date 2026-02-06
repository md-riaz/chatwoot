<script lang="ts">
  import { BarChart3 } from 'lucide-svelte';
  
  interface Props {
    headerTitle: string;
    subtitle?: string;
  }
  
  let { headerTitle, subtitle = 'Monitor team performance and conversation metrics' }: Props = $props();
</script>

<div class="header mb-6 border-b border-slate-200 dark:border-slate-700 pb-6">
  <div class="flex items-start justify-between gap-4">
    <div class="flex-1">
      <div class="flex items-center gap-3 mb-2">
        <BarChart3 class="h-8 w-8 text-blue-600" />
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
          {headerTitle}
        </h1>
      </div>
      <p class="text-slate-600 dark:text-slate-400">
        {subtitle}
      </p>
    </div>
  </div>
</div>