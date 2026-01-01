<script lang="ts">
	import { Table } from '$lib/components/ui/table';
	import { Button } from '$lib/components/ui/button';
	import { Skeleton } from '$lib/components/ui/skeleton';
	import { ChevronLeft, ChevronRight } from 'lucide-svelte';
	
	interface Column {
		key: string;
		label: string;
		sortable?: boolean;
		render?: (value: any, row: any) => string;
	}
	
	interface DataTableProps {
		columns: Column[];
		data: any[];
		loading?: boolean;
		pagination?: {
			page: number;
			perPage: number;
			total: number;
		};
		onSort?: (key: string) => void;
		onPageChange?: (page: number) => void;
		onRowClick?: (row: any) => void;
	}
	
	let {
		columns,
		data = [],
		loading = false,
		pagination,
		onSort,
		onPageChange,
		onRowClick
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
	
	const totalPages = pagination
		? Math.ceil(pagination.total / pagination.perPage)
		: 1;
</script>

<div class="border rounded-lg bg-white dark:bg-slate-1" style="border-color: rgb(var(--slate-6));">
	<Table.Root>
		<Table.Header>
			<Table.Row style="border-color: rgb(var(--slate-6));">
				{#each columns as column}
					<Table.Head
						class="font-medium"
						style="color: rgb(var(--slate-11));"
						onclick={() => column.sortable && handleSort(column.key)}
					>
						{column.label}
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
						<p class="text-sm" style="color: rgb(var(--slate-10));">No data available</p>
					</Table.Cell>
				</Table.Row>
			{:else}
				{#each data as row}
					<Table.Row
						style="border-color: rgb(var(--slate-6));"
						class="cursor-pointer hover:bg-slate-1 dark:hover:bg-slate-2"
						onclick={() => onRowClick && onRowClick(row)}
					>
						{#each columns as column}
							<Table.Cell style="color: rgb(var(--slate-12));">
								{#if column.render}
									{@html column.render(row[column.key], row)}
								{:else}
									{row[column.key] || '-'}
								{/if}
							</Table.Cell>
						{/each}
					</Table.Row>
				{/each}
			{/if}
		</Table.Body>
	</Table.Root>
	
	{#if pagination && totalPages > 1}
		<div class="flex items-center justify-between px-4 py-3 border-t" style="border-color: rgb(var(--slate-6));">
			<div class="text-sm" style="color: rgb(var(--slate-11));">
				Showing {(pagination.page - 1) * pagination.perPage + 1} to {Math.min(pagination.page * pagination.perPage, pagination.total)} of {pagination.total} results
			</div>
			<div class="flex items-center space-x-2">
				<Button
					variant="outline"
					size="sm"
					disabled={pagination.page === 1}
					onclick={() => handlePageChange(pagination!.page - 1)}
				>
					<ChevronLeft class="h-4 w-4" />
					Previous
				</Button>
				<span class="text-sm" style="color: rgb(var(--slate-12));">
					Page {pagination.page} of {totalPages}
				</span>
				<Button
					variant="outline"
					size="sm"
					disabled={pagination.page >= totalPages}
					onclick={() => handlePageChange(pagination!.page + 1)}
				>
					Next
					<ChevronRight class="h-4 w-4" />
				</Button>
			</div>
		</div>
	{/if}
</div>
