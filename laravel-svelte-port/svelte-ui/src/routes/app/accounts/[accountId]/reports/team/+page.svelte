<script lang="ts">
  import { page } from '$app/state';
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';

  const selectedItem = $derived.by(() => {
    const teamId = Number(page.url.searchParams.get('teamId'));
    return Number.isFinite(teamId) && teamId > 0 ? { id: teamId } : null;
  });
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="team"
      downloadButtonLabel="Download Team Reports"
      reportTitle="Team Reports"
      {selectedItem}
    />
  </div>
</div>
