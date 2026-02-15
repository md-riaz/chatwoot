<script lang="ts">
  import { TablePaginationControls } from '$lib/components/ui/pagination';
  import EmptyState from '../shared/EmptyState.svelte';
  import ErrorBoundary from '../shared/ErrorBoundary.svelte';
  import LoadingSkeleton from '../shared/LoadingSkeleton.svelte';

  interface Team {
    id: number;
    name: string;
  }

  interface TeamMetric {
    teamId: number;
    open: number;
    unattended: number;
  }

  interface Props {
    teams: Team[];
    teamMetrics: TeamMetric[];
    isLoading?: boolean;
    error?: string | null;
    onRetry?: () => void;
  }

  let {
    teams,
    teamMetrics,
    isLoading = false,
    error = null,
    onRetry,
  }: Props = $props();

  // Pagination state
  let currentPage = $state(1);
  let pageSize = $state(10);

  // Get team metrics by ID
  function getTeamMetrics(teamId: number): TeamMetric {
    // Handle undefined teamMetrics array
    if (!teamMetrics || !Array.isArray(teamMetrics)) {
      return {
        teamId: teamId,
        open: 0,
        unattended: 0,
      };
    }

    return (
      teamMetrics.find(m => m.teamId === teamId) || {
        teamId: teamId,
        open: 0,
        unattended: 0,
      }
    );
  }

  // Transform data for table
  const tableData = $derived.by(() => {
    return teams
      .map(team => {
        const metrics = getTeamMetrics(team.id);
        return {
          id: team.id,
          name: team.name,
          open: metrics.open,
          unattended: metrics.unattended,
        };
      })
      .sort((a, b) => {
        // Sort by open conversations (descending), then by name (ascending)
        const openDiff = b.open - a.open;
        if (openDiff === 0) {
          return a.name.localeCompare(b.name);
        }
        return openDiff;
      });
  });

  // Pagination
  const totalItems = $derived(tableData.length);
  const paginatedData = $derived.by(() => {
    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;
    return tableData.slice(start, end);
  });

  function handlePageChange(page: number) {
    currentPage = page;
  }

  // Handle page size change and persist to localStorage
  function handlePageSizeChange(newPageSize: number) {
    pageSize = newPageSize;
    currentPage = 1; // Reset to first page

    // Persist to localStorage (matching Vue UI settings pattern)
    try {
      localStorage.setItem(
        'report_overview_team_table_page_size',
        newPageSize.toString()
      );
    } catch (error) {
      console.warn('Failed to save page size to localStorage:', error);
    }
  }

  // Load saved page size on mount
  $effect(() => {
    try {
      const saved = localStorage.getItem(
        'report_overview_team_table_page_size'
      );
      if (saved) {
        pageSize = parseInt(saved, 10) || 10;
      }
    } catch (error) {
      console.warn('Failed to load page size from localStorage:', error);
    }
  });
</script>

<div class="flex flex-col flex-1">
  <ErrorBoundary {error} {onRetry}>
    {#if isLoading}
      <LoadingSkeleton type="table" rows={5} columns={3} />
    {:else if !teams.length}
      <EmptyState
        title="No teams found"
        description="There are no teams to display conversation metrics for. Teams will appear here once they are created and start handling conversations."
        icon="teams"
      />
    {:else}
      <!-- Team table -->
      <div
        class="overflow-auto rounded-lg border border-slate-200 dark:border-slate-700"
      >
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-800">
            <tr>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                style:width="250px"
              >
                Team
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                style:width="100px"
              >
                Open
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                style:width="100px"
              >
                Unattended
              </th>
            </tr>
          </thead>
          <tbody
            class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-700"
          >
            {#each paginatedData as row}
              <tr
                class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
              >
                <td class="px-4 py-3 text-sm">
                  <span class="text-slate-900 dark:text-slate-100 font-medium">
                    {row.name}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm">
                  <span
                    class={row.open
                      ? 'text-slate-900 dark:text-slate-100 font-medium'
                      : 'text-slate-500 dark:text-slate-500'}
                  >
                    {row.open || '---'}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm">
                  <span
                    class={row.unattended
                      ? 'text-slate-900 dark:text-slate-100 font-medium'
                      : 'text-slate-500 dark:text-slate-500'}
                  >
                    {row.unattended || '---'}
                  </span>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>

      <TablePaginationControls
        {currentPage}
        {totalItems}
        itemsPerPage={pageSize}
        onPageChange={handlePageChange}
        onPageSizeChange={handlePageSizeChange}
      />
    {/if}
  </ErrorBoundary>
</div>
