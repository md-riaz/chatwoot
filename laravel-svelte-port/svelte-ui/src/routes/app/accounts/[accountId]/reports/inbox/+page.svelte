<script lang="ts">
  import WootReports from '$lib/components/reports/shared/WootReports.svelte';
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <WootReports
      type="inbox"
      getterKey="inboxes/getInboxes"
      actionKey="inboxes/get"
      downloadButtonLabel="Download Inbox Reports"
      reportTitle="Inbox Reports"
    />
  </div>
</div>
