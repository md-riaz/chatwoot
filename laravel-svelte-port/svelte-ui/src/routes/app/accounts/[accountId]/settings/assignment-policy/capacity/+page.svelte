<script lang="ts">
  /**
   * Agent Capacity Policy Stub Page
   * Placeholder for the Agent Capacity feature (separate follow-up)
   */

  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { ChevronLeft, Construction } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId);

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/assignment-policy`);
  }
</script>

<div class="flex flex-col max-w-4xl mx-auto w-full p-6">
  <!-- Breadcrumb header -->
  <div class="flex items-center gap-2 mb-6">
    <Button variant="ghost" size="sm" onclick={handleBack} class="gap-1">
      <ChevronLeft class="h-4 w-4" />
      Assignment Policy
    </Button>
    <span class="text-muted-foreground">/</span>
    <span class="font-medium">Agent Capacity Policy</span>
  </div>

  <Card.Root>
    <Card.Content class="flex flex-col items-center justify-center py-16 gap-4">
      <Construction class="h-12 w-12 text-muted-foreground" />
      <h2 class="text-xl font-semibold">Coming Soon</h2>
      <p class="text-muted-foreground text-center max-w-md">
        Agent Capacity Policy management is under development. This feature will
        allow you to set conversation capacity limits per agent and configure
        exclusion rules.
      </p>
      <Button variant="outline" onclick={handleBack}
        >Back to Assignment Policy</Button
      >
    </Card.Content>
  </Card.Root>
</div>
