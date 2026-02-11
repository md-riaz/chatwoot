<script lang="ts">
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="team"
      downloadButtonLabel="Download Team Reports"
      reportTitle="Team Reports"
    />
  </div>
</div>
