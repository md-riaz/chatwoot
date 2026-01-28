<script lang="ts">
  /**
   * Settings Layout
   * Two-column layout with navigation sidebar and content area
   */
  
  import type { Snippet } from 'svelte';
  import { page } from '$app/stores';
  import SettingsNav from '$lib/components/settings/SettingsNav.svelte';
  import { ArrowLeft } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { goto } from '$app/navigation';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
  
  // Get accountId from route params
  const accountId = $derived($page.params.accountId);
  const basePath = $derived(`/app/accounts/${accountId}/settings`);
</script>

<div class="flex h-full">
  <!-- Settings Navigation Sidebar -->
  <div class="w-64 border-r bg-background p-4">
    <div class="mb-4">
      <Button
        variant="ghost"
        size="sm"
        class="gap-2"
        onclick={() => goto(`/app/accounts/${accountId}`)}
      >
        <ArrowLeft class="h-4 w-4" />
        Back to Dashboard
      </Button>
    </div>
    
    <h2 class="text-lg font-semibold mb-4">Settings</h2>
    
    <SettingsNav {basePath} />
  </div>
  
  <!-- Settings Content Area -->
  <div class="flex-1 overflow-y-auto">
    <div class="container max-w-[60rem] py-8">
      {@render children()}
    </div>
  </div>
</div>
