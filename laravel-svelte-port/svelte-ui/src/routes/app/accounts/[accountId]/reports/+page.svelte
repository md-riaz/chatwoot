<script lang="ts">
  import { onMount } from 'svelte';
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import StatsLiveReportsContainer from '$lib/components/reports/overview/StatsLiveReportsContainer.svelte';
  import ConversationHeatmapContainer from '$lib/components/reports/heatmaps/ConversationHeatmapContainer.svelte';
  import ResolutionHeatmapContainer from '$lib/components/reports/heatmaps/ResolutionHeatmapContainer.svelte';
  import AgentLiveReportContainer from '$lib/components/reports/overview/AgentLiveReportContainer.svelte';
  import TeamLiveReportContainer from '$lib/components/reports/overview/TeamLiveReportContainer.svelte';
</script>

<div class="reports-page">
  <ReportHeader headerTitle="Reports & Analytics" />
  
  <div class="flex flex-col gap-4 pb-6">
    <!-- Stats Section (Account Metrics + Agent Status) -->
    <StatsLiveReportsContainer />
    
    <!-- Heatmap Visualizations -->
    <ConversationHeatmapContainer />
    <ResolutionHeatmapContainer />
    
    <!-- Agent Performance Table -->
    <AgentLiveReportContainer />
    
    <!-- Team Performance Table -->
    <TeamLiveReportContainer />
  </div>
</div>

<style>
  .reports-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
  }
</style>
