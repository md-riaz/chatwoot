<script lang="ts">
  import DataTable from '$lib/components/DataTable.svelte';
  import AgentCell from './AgentCell.svelte';
  import EmptyState from '../shared/EmptyState.svelte';
  import ErrorBoundary from '../shared/ErrorBoundary.svelte';
  import LoadingSkeleton from '../shared/LoadingSkeleton.svelte';
  import { Button } from '$lib/components/ui/button';
  
  interface Agent {
    id: number;
    name: string;
    availableName?: string;
    email: string;
    thumbnail?: string;
    availabilityStatus?: 'online' | 'busy' | 'offline';
  }
  
  interface AgentMetric {
    assigneeId: number;
    open: number;
    unattended: number;
  }
  
  interface Props {
    agents: Agent[];
    agentMetrics: AgentMetric[];
    isLoading?: boolean;
    error?: string | null;
    onRetry?: () => void;
  }
  
  let { agents, agentMetrics, isLoading = false, error = null, onRetry }: Props = $props();
  
  // Pagination state
  let currentPage = $state(1);
  let pageSize = $state(10);
  
  // Get agent metrics by ID
  function getAgentMetrics(agentId: number): AgentMetric {
    return agentMetrics.find(m => m.assigneeId === agentId) || {
      assigneeId: agentId,
      open: 0,
      unattended: 0
    };
  }
  
  // Transform data for table
  const tableData = $derived(() => {
    return agents
      .map(agent => {
        const metrics = getAgentMetrics(agent.id);
        return {
          id: agent.id,
          agent: agent,
          open: metrics.open,
          unattended: metrics.unattended
        };
      })
      .sort((a, b) => {
        // Sort by open conversations (descending), then by name (ascending)
        const openDiff = b.open - a.open;
        if (openDiff === 0) {
          const nameA = a.agent.availableName || a.agent.name;
          const nameB = b.agent.availableName || b.agent.name;
          return nameA.localeCompare(nameB);
        }
        return openDiff;
      });
  });
  
  // Pagination
  const totalItems = $derived(tableData.length);
  const totalPages = $derived(Math.ceil(totalItems / pageSize));
  const paginatedData = $derived(() => {
    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;
    return tableData.slice(start, end);
  });
  
  const pagination = $derived(() => ({
    page: currentPage,
    perPage: pageSize,
    total: totalItems
  }));
  
  // Table columns
  const columns = [
    {
      key: 'agent',
      label: 'Agent',
      sortable: false,
      width: '250px',
      render: (value: Agent) => {
        // Return a placeholder that will be replaced with the component
        return `<agent-cell data-agent-id="${value.id}"></agent-cell>`;
      }
    },
    {
      key: 'open',
      label: 'Open',
      sortable: false,
      width: '100px',
      render: (value: number) => value ? value.toString() : '---'
    },
    {
      key: 'unattended',
      label: 'Unattended', 
      sortable: false,
      width: '100px',
      render: (value: number) => value ? value.toString() : '---'
    }
  ];
  
  function handlePageChange(page: number) {
    currentPage = page;
  }
  
  // Handle page size change and persist to localStorage
  function handlePageSizeChange(newPageSize: number) {
    pageSize = newPageSize;
    currentPage = 1; // Reset to first page
    
    // Persist to localStorage (matching Vue UI settings pattern)
    try {
      localStorage.setItem('report_overview_agent_table_page_size', newPageSize.toString());
    } catch (error) {
      console.warn('Failed to save page size to localStorage:', error);
    }
  }
  
  // Load saved page size on mount
  $effect(() => {
    try {
      const saved = localStorage.getItem('report_overview_agent_table_page_size');
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
    {:else if !agents.length}
      <EmptyState
        title="No agents found"
        description="There are no agents to display conversation metrics for. Agents will appear here once they start handling conversations."
        icon="users"
      />
    {:else}
      <!-- Custom table implementation to handle AgentCell component -->
      <div class="overflow-auto rounded-lg border border-slate-200 dark:border-slate-700">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-800">
            <tr>
              {#each columns as column}
                <th 
                  class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                  style:width={column.width}
                >
                  {column.label}
                </th>
              {/each}
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-700">
            {#each paginatedData as row}
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <td class="px-4 py-3 text-sm">
                  <AgentCell agent={row.agent} />
                </td>
                <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                  <span class={row.open ? 'text-slate-900 dark:text-slate-100 font-medium' : 'text-slate-500 dark:text-slate-500'}>
                    {row.open || '---'}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                  <span class={row.unattended ? 'text-slate-900 dark:text-slate-100 font-medium' : 'text-slate-500 dark:text-slate-500'}>
                    {row.unattended || '---'}
                  </span>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      {#if totalPages > 1}
        <div class="flex items-center justify-between px-2 mt-4">
          <div class="text-sm text-slate-700 dark:text-slate-300">
            Showing {(currentPage - 1) * pageSize + 1} to {Math.min(currentPage * pageSize, totalItems)} of {totalItems} results
          </div>
          <div class="flex items-center gap-2">
            <!-- Page size selector -->
            <select 
              bind:value={pageSize}
              on:change={(e) => handlePageSizeChange(parseInt(e.currentTarget.value))}
              class="text-sm border border-slate-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"
            >
              <option value={10}>10 per page</option>
              <option value={25}>25 per page</option>
              <option value={50}>50 per page</option>
              <option value={100}>100 per page</option>
            </select>
            
            <!-- Page navigation -->
            <Button
              variant="outline"
              size="sm"
              onclick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage <= 1}
            >
              Previous
            </Button>
            <div class="text-sm text-slate-700 dark:text-slate-300">
              Page {currentPage} of {totalPages}
            </div>
            <Button
              variant="outline"
              size="sm"
              onclick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage >= totalPages}
            >
              Next
            </Button>
          </div>
        </div>
      {/if}
    {/if}
  </ErrorBoundary>
</div>