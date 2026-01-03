<script lang="ts">
	import { onMount } from 'svelte';
	import { Search, MessageSquare, Edit, Trash2, Copy, Plus } from '@lucide/svelte';
	import * as Card from '$lib/components/ui/card';
	import * as Tabs from '$lib/components/ui/tabs';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Badge } from '$lib/components/ui/badge';
	import { Skeleton } from '$lib/components/ui/skeleton';

	type Category = 'all' | 'general' | 'sales' | 'support' | 'billing';

	interface CannedResponse {
		id: number;
		shortCode: string;
		title: string;
		content: string;
		category: string;
		updatedAt: string;
	}

	// State for canned responses (placeholder - will be replaced with store)
	let cannedResponses = $state<CannedResponse[]>([
		{
			id: 1,
			shortCode: '/hello',
			title: 'Greeting',
			content: 'Hello! How can I help you today?',
			category: 'general',
			updatedAt: '2024-01-03'
		},
		{
			id: 2,
			shortCode: '/pricing',
			title: 'Pricing Information',
			content:
				'Our pricing plans start at $29/month for the Basic plan, $79/month for Pro, and $199/month for Enterprise. Would you like to learn more about any specific plan?',
			category: 'sales',
			updatedAt: '2024-01-02'
		},
		{
			id: 3,
			shortCode: '/troubleshoot',
			title: 'Troubleshooting Steps',
			content:
				'Let me help you troubleshoot this issue. First, could you please try clearing your browser cache and cookies? Then, log out and log back in. If the issue persists, please let me know.',
			category: 'support',
			updatedAt: '2024-01-01'
		},
		{
			id: 4,
			shortCode: '/refund',
			title: 'Refund Policy',
			content:
				'We offer a 30-day money-back guarantee. To process your refund, I will need your order number and the reason for the refund. Could you please provide those details?',
			category: 'billing',
			updatedAt: '2023-12-30'
		}
	]);

	let searchQuery = $state('');
	let activeCategory = $state<Category>('all');
	let isLoading = $state(false);

	// Reactive filtered responses
	const filteredResponses = $derived(() => {
		let filtered = cannedResponses;

		// Filter by category
		if (activeCategory !== 'all') {
			filtered = filtered.filter((r) => r.category === activeCategory);
		}

		// Filter by search query
		if (searchQuery.trim()) {
			const query = searchQuery.toLowerCase();
			filtered = filtered.filter(
				(r) =>
					r.shortCode.toLowerCase().includes(query) || r.content.toLowerCase().includes(query)
			);
		}

		return filtered;
	});

	// Get category count
	function getCategoryCount(category: Category): number {
		if (category === 'all') return cannedResponses.length;
		return cannedResponses.filter((r) => r.category === category).length;
	}

	// Copy short code to clipboard
	function copyShortCode(code: string) {
		navigator.clipboard.writeText(code);
		// TODO: Show toast notification
	}

	// Truncate content
	function truncate(text: string, maxLength: number): string {
		if (text.length <= maxLength) return text;
		return text.slice(0, maxLength) + '...';
	}

	onMount(() => {
		// TODO: Fetch canned responses from store
	});
</script>

<div class="container mx-auto p-6">
	<!-- Header -->
	<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
		<div>
			<h1 class="text-3xl font-bold">Canned Responses</h1>
			<p class="text-muted-foreground">Save time with pre-written responses</p>
		</div>
		<Button>
			<Plus class="mr-2 h-4 w-4" />
			New Response
		</Button>
	</div>

	<!-- Search -->
	<div class="mb-6">
		<div class="relative">
			<Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
			<Input
				type="text"
				placeholder="Search by short code or content..."
				bind:value={searchQuery}
				class="pl-10"
			/>
		</div>
	</div>

	<!-- Category tabs -->
	<Tabs.Root value={activeCategory} class="mb-6">
		<Tabs.List>
			<Tabs.Trigger value="all" onclick={() => (activeCategory = 'all')}>
				All ({getCategoryCount('all')})
			</Tabs.Trigger>
			<Tabs.Trigger value="general" onclick={() => (activeCategory = 'general')}>
				General ({getCategoryCount('general')})
			</Tabs.Trigger>
			<Tabs.Trigger value="sales" onclick={() => (activeCategory = 'sales')}>
				Sales ({getCategoryCount('sales')})
			</Tabs.Trigger>
			<Tabs.Trigger value="support" onclick={() => (activeCategory = 'support')}>
				Support ({getCategoryCount('support')})
			</Tabs.Trigger>
			<Tabs.Trigger value="billing" onclick={() => (activeCategory = 'billing')}>
				Billing ({getCategoryCount('billing')})
			</Tabs.Trigger>
		</Tabs.List>
	</Tabs.Root>

	<!-- Response count -->
	<div class="mb-4">
		<p class="text-sm text-muted-foreground">
			{filteredResponses().length}
			{filteredResponses().length === 1 ? 'response' : 'responses'}
		</p>
	</div>

	<!-- Loading state -->
	{#if isLoading}
		<div class="grid gap-4">
			{#each Array(6) as _}
				<Card.Root>
					<Card.Content class="p-4">
						<div class="space-y-3">
							<Skeleton class="h-6 w-24" />
							<Skeleton class="h-5 w-48" />
							<Skeleton class="h-4 w-full" />
							<Skeleton class="h-4 w-3/4" />
						</div>
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{:else if filteredResponses().length === 0}
		<!-- Empty state -->
		<Card.Root>
			<Card.Content class="flex flex-col items-center justify-center py-12">
				<MessageSquare class="mb-4 h-12 w-12 text-muted-foreground" />
				<h3 class="mb-2 text-lg font-semibold">
					{searchQuery || activeCategory !== 'all' ? 'No responses found' : 'No canned responses yet'}
				</h3>
				<p class="mb-4 text-center text-sm text-muted-foreground">
					{searchQuery || activeCategory !== 'all'
						? 'Try adjusting your filters or search query'
						: 'Create canned responses to save time when replying to common questions'}
				</p>
				{#if !searchQuery && activeCategory === 'all'}
					<Button>
						<Plus class="mr-2 h-4 w-4" />
						Create Response
					</Button>
				{/if}
			</Card.Content>
		</Card.Root>
	{:else}
		<!-- Responses list -->
		<div class="grid gap-4">
			{#each filteredResponses() as response (response.id)}
				<Card.Root class="transition-colors hover:border-primary">
					<Card.Content class="p-4">
						<div class="flex items-start justify-between gap-4">
							<div class="flex-1 min-w-0">
								<!-- Short code badge -->
								<Badge variant="secondary" class="mb-2 font-mono text-xs">
									{response.shortCode}
								</Badge>

								<!-- Title -->
								<h3 class="mb-2 font-semibold">{response.title}</h3>

								<!-- Content preview -->
								<p class="mb-3 text-sm text-muted-foreground">
									{truncate(response.content, 120)}
								</p>

								<!-- Category and date -->
								<div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
									<Badge variant="outline" class="capitalize">{response.category}</Badge>
									<span>•</span>
									<span>Updated {response.updatedAt}</span>
								</div>
							</div>

							<!-- Actions -->
							<div class="flex gap-2">
								<Button
									variant="outline"
									size="sm"
									onclick={() => copyShortCode(response.shortCode)}
									title="Copy short code"
								>
									<Copy class="h-4 w-4" />
								</Button>
								<Button variant="outline" size="sm" title="Edit response">
									<Edit class="h-4 w-4" />
								</Button>
								<Button variant="outline" size="sm" title="Delete response">
									<Trash2 class="h-4 w-4" />
								</Button>
							</div>
						</div>
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{/if}
</div>
