<script lang="ts">
  import { page } from '$app/state';
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';

  const selectedItem = $derived.by(() => {
    const labelId = Number(page.url.searchParams.get('labelId'));
    return Number.isFinite(labelId) && labelId > 0 ? { id: labelId } : null;
  });
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="label"
      downloadButtonLabel="Download Label Reports"
      reportTitle="Label Reports"
      {selectedItem}
    />
  </div>
</div>
