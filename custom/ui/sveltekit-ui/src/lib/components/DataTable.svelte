<script lang="ts">
	import { Table } from '$lib/components/ui/table';
	import { Button } from '$lib/components/ui/button';
	import { Skeleton } from '$lib/components/ui/skeleton';
	import { ChevronLeft, ChevronRight, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-svelte';
	import type { DataTableColumn } from '$lib/types';
	
	interface DataTableProps<T = Record<string, unknown>> {
		columns: DataTableColumn<T>[];
		data: T[];
		loading?: boolean;
		pagination?: {
			page: number;
			perPage: number;
			total: number;
		};
		sortState?: {
			column: string;
			direction: 'asc' | 'desc' | null;
		};
		onSort?: (key: string) => void;
		onPageChange?: (page: number) => void;
		onRowClick?: (row: T) => void;
		emptyMessage?: string;
		ariaLabel?: string;
	}
	
	let {
		columns,
		data = [],
		loading = false,
		pagination,
		sortState,
		onSort,
		onPageChange,
		onRowClick,
		emptyMessage = 'No data available',
		ariaLabel = 'Data table'
	}: DataTableProps = $props();
	
	function handleSort(key: string) {
		if (onSort) {
			onSort(key);
		}
	}
	
	function handlePageChange(newPage: number) {
		if (onPageChange && pagination) {
			onPageChange(newPage);
		}
	}
	
	function getSortIcon(columnKey: string) {
		if (!sortState || sortState.column !== columnKey) {
			return ArrowUpDown;
		}
		return sortState.direction === 'asc' ? ArrowUp : ArrowDown;
	}
	
	const totalPages = pagination
		? Math.ceil(pagination.total / pagination.perPage)
		: 1;
</script>

<div 
	class="border rounded-lg bg-white dark:bg-slate-1" 
	style="border-color: rgb(var(--slate-6));"
	role="region"
	aria-label={ariaLabel}
>
	<Table.Root>
		<Table.Header>
			<Table.Row style="border-color: rgb(var(--slate-6));">
				{#each columns as column}
					<Table.Head
						class="font-medium"
						style="color: rgb(var(--slate-11)); {column.width ? `width: ${column.width};` : ''}"
						onclick={() => column.sortable && handleSort(column.key)}
						role={column.sortable ? 'button' : undefined}
						tabindex={column.sortable ? 0 : undefined}
						onkeydown={(e) => {
							if (column.sortable && (e.key === 'Enter' || e.key === ' ')) {
								e.preventDefault();
								handleSort(column.key);
							}
						}}
						aria-sort={sortState?.column === column.key 
							? sortState.direction === 'asc' ? 'ascending' : 'descending'
							: undefined}
					>
						<div class="flex items-center gap-2">
							{column.label}
							{#if column.sortable}
								{@const Icon = getSortIcon(column.key)}
								<Icon class="h-4 w-4" aria-hidden="true" />
							{/if}
						</div>
					</Table.Head>
				{/each}
			</Table.Row>
		</Table.Header>
		<Table.Body>
			{#if loading}
				{#each Array(5) as _}
					<Table.Row style="border-color: rgb(var(--slate-6));">
						{#each columns as _}
							<Table.Cell>
								<Skeleton class="h-5 w-24" />
							</Table.Cell>
						{/each}
					</Table.Row>
				{/each}
			{:else if data.length === 0}
				<Table.Row>
					<Table.Cell colspan={columns.length} class="text-center py-8">
						<p class="text-sm" style="color: rgb(var(--slate-10));" role="status">
							{emptyMessage}
						</p>
					</Table.Cell>
				</Table.Row>
			{:else}
				{#each data as row}
					<Table.Row
						style="border-color: rgb(var(--slate-6));"
						class="{onRowClick ? 'cursor-pointer hover:bg-slate-1 dark:hover:bg-slate-2' : ''}"
						onclick={() => onRowClick && onRowClick(row)}
						role={onRowClick ? 'button' : undefined}
						tabindex={onRowClick ? 0 : undefined}
						onkeydown={(e) => {
							if (onRowClick && (e.key === 'Enter' || e.key === ' ')) {
								e.preventDefault();
								onRowClick(row);
							}
						}}
					>
						{#each columns as column}
							<Table.Cell style="color: rgb(var(--slate-12));">
								{#if column.render}
									{@const renderedValue = column.render(row[column.key], row)}
									{renderedValue ?? '-'}
								{:else}
									{row[column.key] ?? '-'}
								{/if}
							</Table.Cell>
						{/each}
					</Table.Row>
				{/each}
			{/if}
		</Table.Body>
	</Table.Root>
	
	{#if pagination && totalPages > 1}
		<nav 
			class="flex items-center justify-between px-4 py-3 border-t" 
			style="border-color: rgb(var(--slate-6));"
			aria-label="Pagination"
		>
			<div class="text-sm" style="color: rgb(var(--slate-11));" role="status" aria-live="polite">
				Showing {(pagination.page - 1) * pagination.perPage + 1} to {Math.min(pagination.page * pagination.perPage, pagination.total)} of {pagination.total} results
			</div>
			<div class="flex items-center space-x-2">
				<Button
					variant="outline"
					size="sm"
					disabled={pagination.page === 1}
					onclick={() => handlePageChange(pagination!.page - 1)}
					aria-label="Go to previous page"
				>
					<ChevronLeft class="h-4 w-4" aria-hidden="true" />
					Previous
				</Button>
				<span class="text-sm" style="color: rgb(var(--slate-12));" role="status">
					Page {pagination.page} of {totalPages}
				</span>
				<Button
					variant="outline"
					size="sm"
					disabled={pagination.page >= totalPages}
					onclick={() => handlePageChange(pagination!.page + 1)}
					aria-label="Go to next page"
				>
					Next
					<ChevronRight class="h-4 w-4" aria-hidden="true" />
				</Button>
			</div>
		</nav>
	{/if}
</div>
