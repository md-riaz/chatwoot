<script lang="ts">
  import BaseHeatmapContainer from './BaseHeatmapContainer.svelte';
</script>

<BaseHeatmapContainer
  metric="conversations_count"
  title="Conversation Traffic"
  downloadTitle="conversation_heatmap"
  storeGetter="accountConversationHeatmapData"
  storeAction="fetchAccountConversationHeatmap"
  downloadAction="downloadAccountConversationHeatmap"
  uiFlagKey="isFetchingAccountConversationsHeatmap"
  colorScheme="blue"
/>