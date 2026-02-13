<script lang="ts">
  import { page } from '$app/state';
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';

  const selectedItem = $derived.by(() => {
    const inboxId = Number(page.url.searchParams.get('inboxId'));
    return Number.isFinite(inboxId) && inboxId > 0 ? { id: inboxId } : null;
  });
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="inbox"
      downloadButtonLabel="Download Inbox Reports"
      reportTitle="Inbox Reports"
      {selectedItem}
    />
  </div>
</div>
