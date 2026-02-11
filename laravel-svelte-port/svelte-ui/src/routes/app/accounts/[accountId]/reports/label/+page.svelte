<script lang="ts">
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="label"
      downloadButtonLabel="Download Label Reports"
      reportTitle="Label Reports"
    />
  </div>
</div>
