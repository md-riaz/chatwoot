<script lang="ts">
	import { onMount } from 'svelte';
	import {
		MessageCircle,
		Mail,
		Facebook,
		Twitter,
		Send,
		Puzzle,
		Zap,
		Webhook,
		Plus,
		Settings,
		CheckCircle
	} from '@lucide/svelte';
	import * as Card from '$lib/components/ui/card';
	import { Button } from '$lib/components/ui/button';
	import { Badge } from '$lib/components/ui/badge';
	import { Skeleton } from '$lib/components/ui/skeleton';

	type IntegrationStatus = 'connected' | 'available';

	interface Integration {
		id: string;
		name: string;
		description: string;
		icon: any;
		status: IntegrationStatus;
	}

	// State for integrations (placeholder - will be replaced with store)
	let integrations = $state<Integration[]>([
		{
			id: 'slack',
			name: 'Slack',
			description: 'Get notified about new conversations in your Slack workspace',
			icon: MessageCircle,
			status: 'connected'
		},
		{
			id: 'whatsapp',
			name: 'WhatsApp',
			description: 'Connect your WhatsApp Business account to receive messages',
			icon: MessageCircle,
			status: 'available'
		},
		{
			id: 'facebook',
			name: 'Facebook Messenger',
			description: 'Respond to Facebook messages directly from Chatwoot',
			icon: Facebook,
			status: 'available'
		},
		{
			id: 'email',
			name: 'Email',
			description: 'Forward emails to your Chatwoot inbox',
			icon: Mail,
			status: 'connected'
		},
		{
			id: 'telegram',
			name: 'Telegram',
			description: 'Connect your Telegram bot to receive messages',
			icon: Send,
			status: 'available'
		},
		{
			id: 'twitter',
			name: 'Twitter',
			description: 'Respond to Twitter mentions and direct messages',
			icon: Twitter,
			status: 'available'
		},
		{
			id: 'zapier',
			name: 'Zapier',
			description: 'Connect with 5000+ apps using Zapier workflows',
			icon: Zap,
			status: 'available'
		},
		{
			id: 'webhooks',
			name: 'Webhooks',
			description: 'Send real-time data to external services',
			icon: Webhook,
			status: 'connected'
		}
	]);

	let isLoading = $state(false);

	// Get connected integrations count
	const connectedCount = $derived(
		integrations.filter((i) => i.status === 'connected').length
	);

	// Get badge variant based on status
	function getBadgeVariant(status: IntegrationStatus): 'default' | 'secondary' {
		return status === 'connected' ? 'default' : 'secondary';
	}

	// Get button text based on status
	function getButtonText(status: IntegrationStatus): string {
		return status === 'connected' ? 'Configure' : 'Connect';
	}

	// Get badge icon
	function getBadgeIcon(status: IntegrationStatus) {
		return status === 'connected' ? CheckCircle : Plus;
	}

	onMount(() => {
		// TODO: Fetch integrations from store
	});
</script>

<div class="container mx-auto p-6">
	<!-- Header -->
	<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
		<div>
			<h1 class="text-3xl font-bold">Integrations</h1>
			<p class="text-muted-foreground">Connect your favorite tools and channels</p>
		</div>
	</div>

	<!-- Integration count -->
	<div class="mb-4">
		<p class="text-sm text-muted-foreground">
			{connectedCount} of {integrations.length} integrations connected
		</p>
	</div>

	<!-- Loading state -->
	{#if isLoading}
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			{#each Array(6) as _}
				<Card.Root>
					<Card.Content class="p-6">
						<div class="space-y-3">
							<Skeleton class="h-12 w-12 rounded-lg" />
							<Skeleton class="h-6 w-32" />
							<Skeleton class="h-4 w-full" />
							<Skeleton class="h-9 w-24" />
						</div>
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{:else if integrations.length === 0}
		<!-- Empty state -->
		<Card.Root>
			<Card.Content class="flex flex-col items-center justify-center py-12">
				<Puzzle class="mb-4 h-12 w-12 text-muted-foreground" />
				<h3 class="mb-2 text-lg font-semibold">No integrations available</h3>
				<p class="text-center text-sm text-muted-foreground">
					Check back later for new integrations
				</p>
			</Card.Content>
		</Card.Root>
	{:else}
		<!-- Integrations grid -->
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			{#each integrations as integration (integration.id)}
				<Card.Root class="transition-all hover:scale-105 hover:shadow-md">
					<Card.Content class="p-6">
						<!-- Icon -->
						<div
							class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
						>
							<svelte:component this={integration.icon} class="h-6 w-6" />
						</div>

						<!-- Name and badge -->
						<div class="mb-2 flex items-center gap-2">
							<h3 class="font-semibold">{integration.name}</h3>
							<Badge variant={getBadgeVariant(integration.status)} class="gap-1 text-xs">
								<svelte:component this={getBadgeIcon(integration.status)} class="h-3 w-3" />
								{integration.status === 'connected' ? 'Connected' : 'Available'}
							</Badge>
						</div>

						<!-- Description -->
						<p class="mb-4 text-sm text-muted-foreground">
							{integration.description}
						</p>

						<!-- Action button -->
						<Button
							variant={integration.status === 'connected' ? 'outline' : 'default'}
							size="sm"
							class="w-full"
						>
							{#if integration.status === 'connected'}
								<Settings class="mr-2 h-4 w-4" />
							{:else}
								<Plus class="mr-2 h-4 w-4" />
							{/if}
							{getButtonText(integration.status)}
						</Button>
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{/if}
</div>
