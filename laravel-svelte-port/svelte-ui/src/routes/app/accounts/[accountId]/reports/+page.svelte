<script lang="ts">
  import { onMount } from 'svelte';
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import StatsLiveReportsContainer from '$lib/components/reports/overview/StatsLiveReportsContainer.svelte';
  import ConversationHeatmapContainer from '$lib/components/reports/heatmaps/ConversationHeatmapContainer.svelte';
  import ResolutionHeatmapContainer from '$lib/components/reports/heatmaps/ResolutionHeatmapContainer.svelte';
  import AgentLiveReportContainer from '$lib/components/reports/overview/AgentLiveReportContainer.svelte';
  import TeamLiveReportContainer from '$lib/components/reports/overview/TeamLiveReportContainer.svelte';
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <ReportHeader headerTitle="Overview" />
    <div class="flex flex-col gap-4 pb-6">
      <StatsLiveReportsContainer />
      <ConversationHeatmapContainer />
      <ResolutionHeatmapContainer />
      <AgentLiveReportContainer />
      <TeamLiveReportContainer />
    </div>
  </div>
</div>
