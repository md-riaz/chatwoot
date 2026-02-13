<script lang="ts">
  import { page } from '$app/state';
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';

  const selectedItem = $derived.by(() => {
    const agentId = Number(page.url.searchParams.get('agentId'));
    return Number.isFinite(agentId) && agentId > 0 ? { id: agentId } : null;
  });
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="agent"
      downloadButtonLabel="Download Agent Reports"
      reportTitle="Agent Reports"
      {selectedItem}
    />
  </div>
</div>
