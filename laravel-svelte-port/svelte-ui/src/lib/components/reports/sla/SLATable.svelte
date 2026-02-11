<script lang="ts">
  /**
   * SLATable Component
   * Displays SLA reports in table format with pagination
   * Vue Parity: Replaces SLA table from Vue dashboard
   */
  import { createEventDispatcher } from 'svelte';
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '$lib/components/ui/table';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { ChevronLeft, ChevronRight } from 'lucide-svelte';
  import { getRelativeTime, formatDate } from '$lib/utils/timeHelper';
  import type { SLAReport } from '$lib/stores/slaReports.svelte';

  interface Props {
    slaReports: SLAReport[];
    isLoading?: boolean;
    currentPage: number;
    totalCount: number;
  }

  let {
    slaReports = [],
    isLoading = false,
    currentPage = 1,
    totalCount = 0
  }: Props = $props();

  const dispatch = createEventDispatcher<{
    'page-change': number;
  }>();

  const pageSize = 25;
  const totalPages = $derived(Math.ceil(totalCount / pageSize));
  const hasNextPage = $derived(currentPage < totalPages);
  const hasPrevPage = $derived(currentPage > 1);

  function handlePrevPage() {
    if (hasPrevPage) {
      dispatch('page-change', currentPage - 1);
    }
  }

  function handleNextPage() {
    if (hasNextPage) {
      dispatch('page-change', currentPage + 1);
    }
  }

  function getSLAStatusBadge(status: string) {
    return status === 'hit' 
      ? { variant: 'default' as const, label: 'Hit' }
      : { variant: 'destructive' as const, label: 'Missed' };
  }
</script>

<div class="bg-card rounded-lg border">
  <div class="overflow-x-auto">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Conversation ID</TableHead>
          <TableHead>Contact</TableHead>
          <TableHead>Assignee</TableHead>
          <TableHead>SLA Policy</TableHead>
          <TableHead>Applied At</TableHead>
          <TableHead>Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {#if isLoading}
          <TableRow>
            <TableCell colspan={6} class="text-center py-8 text-muted-foreground">
              Loading SLA reports...
            </TableCell>
          </TableRow>
        {:else if slaReports.length === 0}
          <TableRow>
            <TableCell colspan={6} class="text-center py-8 text-muted-foreground">
              No SLA reports found for the selected filters.
            </TableCell>
          </TableRow>
        {:else}
          {#each slaReports as report}
            <TableRow>
              <TableCell class="font-medium">#{report.conversationId}</TableCell>
              <TableCell>
                <div class="flex flex-col">
                  <span class="font-medium">{report.conversation?.displayId || report.conversationId}</span>
                  <span class="text-xs text-muted-foreground">Conversation {report.conversationId}</span>
                </div>
              </TableCell>
              <TableCell>
                {report.assignedAgent?.name || 'Unassigned'}
              </TableCell>
              <TableCell>{report.slaName}</TableCell>
              <TableCell>
                {formatDate(report.createdAt, 'datetime')}
              </TableCell>
              <TableCell>
                {@const statusBadge = getSLAStatusBadge(report.status)}
                <Badge variant={statusBadge.variant}>
                  {statusBadge.label}
                </Badge>
              </TableCell>
            </TableRow>
          {/each}
        {/if}
      </TableBody>
    </Table>
  </div>

  <!-- Pagination -->
  {#if !isLoading && slaReports.length > 0}
    <div class="flex items-center justify-between px-4 py-3 border-t">
      <div class="text-sm text-muted-foreground">
        Showing {((currentPage - 1) * pageSize) + 1} to {Math.min(currentPage * pageSize, totalCount)} of {totalCount} results
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          onclick={handlePrevPage}
          disabled={!hasPrevPage}
        >
          <ChevronLeft class="h-4 w-4 mr-1" />
          Previous
        </Button>
        <div class="text-sm font-medium">
          Page {currentPage} of {totalPages}
        </div>
        <Button
          variant="outline"
          size="sm"
          onclick={handleNextPage}
          disabled={!hasNextPage}
        >
          Next
          <ChevronRight class="h-4 w-4 ml-1" />
        </Button>
      </div>
    </div>
  {/if}
</div>
