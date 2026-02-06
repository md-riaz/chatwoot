<script lang="ts">
  import BaseHeatmapContainer from './BaseHeatmapContainer.svelte';
</script>

<BaseHeatmapContainer
  metric="resolutions_count"
  title="Resolution Traffic"
  downloadTitle="resolution_heatmap"
  storeGetter="accountResolutionHeatmapData"
  storeAction="fetchAccountResolutionHeatmap"
  uiFlagKey="isFetchingAccountResolutionsHeatmap"
  colorScheme="green"
/>