<script lang="ts">
  import { page } from '$app/stores';
  import { Button } from '$lib/components/ui/button';
  import { cn } from '$lib/utils';
  import { useSidebar } from '$lib/components/ui/sidebar/index.js';

  const sidebar = useSidebar();

  const isConversationDetailRoute = $derived(() => {
    const path = $page.url.pathname;
    return /\/app\/accounts\/\d+\/conversations\/\d+/.test(path);
  });
</script>

{#if !isConversationDetailRoute}
  <div
    id="mobile-sidebar-launcher"
    class={cn(
      'fixed bottom-4 ltr:left-4 rtl:right-4 z-40 block md:hidden transition-transform duration-200 ease-in-out',
      sidebar.openMobile && 'ltr:translate-x-48 rtl:-translate-x-48'
    )}
  >
    <div class="rounded-full bg-background/90 border border-border backdrop-blur px-1.5 py-1 shadow hover:shadow-md">
      <Button
        type="button"
        variant="ghost"
        size="icon"
        class="h-10 w-10 rounded-full bg-muted text-foreground hover:bg-muted/80"
        aria-label="Toggle sidebar"
        onclick={() => sidebar.toggle()}
      >
        <span class="i-lucide-menu text-lg"></span>
      </Button>
    </div>
  </div>
{/if}

