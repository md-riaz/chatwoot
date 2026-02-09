<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { csatStore } from '$lib/stores/csat.svelte';
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '$lib/components/ui/table';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { ChevronLeft, ChevronRight } from 'lucide-svelte';
  import LoadingSkeleton from '../shared/LoadingSkeleton.svelte';

  interface Props {
    pageIndex: number;
  }

  let { pageIndex }: Props = $props();

  const dispatch = createEventDispatcher();

  const responses = $derived(csatStore.getResponses());
  const meta = $derived(csatStore.getMeta());
  const isLoading = $derived(csatStore.getUIFlags().isFetching);

  const totalPages = $derived(Math.ceil((meta?.count || 0) / (meta?.perPage || 25)));

  function getRatingColor(rating: number) {
    if (rating >= 4) return 'bg-green-500';
    if (rating >= 3) return 'bg-yellow-500';
    return 'bg-red-500';
  }

  function onPreviousPage() {
    if (pageIndex > 0) {
      dispatch('page-change', pageIndex - 1);
    }
  }

  function onNextPage() {
    if (pageIndex < totalPages - 1) {
      dispatch('page-change', pageIndex + 1);
    }
  }
</script>

<div class="border rounded-lg">
  {#if isLoading}
    <div class="h-[400px] animate-pulse bg-muted rounded"></div>
  {:else if responses && responses.length > 0}
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Contact</TableHead>
          <TableHead>Agent</TableHead>
          <TableHead>Rating</TableHead>
          <TableHead>Feedback</TableHead>
          <TableHead>Date</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {#each responses as response}
          <TableRow>
            <TableCell>
              <div class="font-medium">{response.contact?.name || 'Unknown'}</div>
              <div class="text-sm text-muted-foreground">{response.contact?.email || ''}</div>
            </TableCell>
            <TableCell>{response.assignedAgent?.name || 'Unassigned'}</TableCell>
            <TableCell>
              <Badge class={getRatingColor(response.rating)}>
                {response.rating}/5
              </Badge>
            </TableCell>
            <TableCell class="max-w-md truncate">{response.feedbackMessage || '-'}</TableCell>
            <TableCell>{new Date(response.createdAt).toLocaleDateString()}</TableCell>
          </TableRow>
        {/each}
      </TableBody>
    </Table>

    <!-- Pagination -->
    <div class="flex items-center justify-between px-4 py-3 border-t">
      <div class="text-sm text-muted-foreground">
        Page {pageIndex + 1} of {totalPages}
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          onclick={onPreviousPage}
          disabled={pageIndex === 0}
        >
          <ChevronLeft class="h-4 w-4" />
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          onclick={onNextPage}
          disabled={pageIndex >= totalPages - 1}
        >
          Next
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  {:else}
    <div class="p-8 text-center text-muted-foreground">
      No CSAT responses found
    </div>
  {/if}
</div>
