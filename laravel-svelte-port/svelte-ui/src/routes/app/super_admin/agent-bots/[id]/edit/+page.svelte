<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { api } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import * as Select from '$lib/components/ui/select';
	import { Textarea } from '$lib/components/ui/textarea';
	import { ArrowLeft, Save, Trash2, Upload, X } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	const botId = $page.params.id;

	let loading = $state(true);
	let loadingAccounts = $state(true);
	let submitting = $state(false);
	let bot: any = null;
	let avatarFile = $state<File | null>(null);
	let avatarPreview = $state<string | null>(null);
	let accounts = $state<any[]>([]);

	let formData = $state({
		name: '',
		description: '',
		outgoingUrl: '',
		accountId: 0 as number // 0 = global bot, >0 = specific account
	});

	// Convert account_id to string for Select component
	let selectedAccountId = $state<string>('0');

	let errors = $state<Record<string, string>>({});

	// Update formData when selectedAccountId changes
	$effect(() => {
		formData.accountId = Number(selectedAccountId);
	});

	// Sync selectedAccountId with formData.accountId (for edit page)
	$effect(() => {
		if (formData.accountId === null || formData.accountId === 0) {
			selectedAccountId = '0';
		} else {
			selectedAccountId = String(formData.accountId);
		}
	});

	// Account selection trigger content
	const accountTriggerContent = $derived.by(() => {
		if (selectedAccountId === '0') {
			return "Global Bot (All Accounts)";
		}
		const account = accounts.find(a => a.id === Number(selectedAccountId));
		return account ? account.name : "Select account";
	});

	onMount(async () => {
		if (!botId) {
			toast.error('Invalid bot ID');
			goto('/app/super_admin/agent-bots');
			return;
		}
		await Promise.all([loadBot(), loadAccounts()]);
	});

	async function loadAccounts() {
		loadingAccounts = true;
		try {
			const response = await api.accounts.list({ per_page: 100 });
			accounts = response.data || [];
		} catch (error: any) {
			toast.error('Failed to load accounts');
			console.error(error);
		} finally {
			loadingAccounts = false;
		}
	}

	async function loadBot() {
		if (!botId) return;
		
		loading = true;
		try {
			bot = await api.agentBots.get(botId);
			formData = {
				name: bot.name || '',
				description: bot.description || '',
				outgoingUrl: bot.outgoingUrl || '',
				accountId: bot.accountId || 0 // Convert null to 0 for global bot
			};
			avatarPreview = bot.avatarUrl || null;
		} catch (error: any) {
			toast.error('Failed to load agent bot: ' + (error.message || 'Unknown error'));
			goto('/app/super_admin/agent-bots');
		} finally {
			loading = false;
		}
	}

	async function handleSubmit(e: Event) {
		e.preventDefault();
		if (!botId) return;
		
		errors = {};

		if (!formData.name.trim()) {
			errors.name = 'Bot name is required';
			return;
		}

		submitting = true;
		try {
			await api.agentBots.update(botId, formData);

			if (avatarFile) {
				await api.agentBots.uploadAvatar(botId, avatarFile);
			}

			toast.success('Agent bot updated successfully');
			goto(`/app/super_admin/agent-bots/${botId}`); // Go back to details page
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to update agent bot');
			}
		} finally {
			submitting = false;
		}
	}

	async function handleDelete() {
		if (!botId) return;
		
		if (!confirm('Are you sure you want to delete this agent bot? This action cannot be undone.')) {
			return;
		}

		try {
			await api.agentBots.delete(botId);
			toast.success('Agent bot deleted successfully');
			goto('/app/super_admin/agent-bots');
		} catch (error: any) {
			toast.error('Failed to delete agent bot: ' + (error.message || 'Unknown error'));
		}
	}

	function handleAvatarSelect(event: Event) {
		const input = event.target as HTMLInputElement;
		if (input.files && input.files[0]) {
			avatarFile = input.files[0];
			const reader = new FileReader();
			reader.onload = (e) => {
				avatarPreview = e.target?.result as string;
			};
			reader.readAsDataURL(avatarFile);
		}
	}

	async function handleAvatarDelete() {
		if (!botId) return;
		
		if (!confirm('Are you sure you want to delete the avatar?')) {
			return;
		}

		try {
			await api.agentBots.deleteAvatar(botId);
			avatarPreview = null;
			avatarFile = null;
			toast.success('Avatar deleted successfully');
		} catch (error: any) {
			toast.error('Failed to delete avatar: ' + (error.message || 'Unknown error'));
		}
	}

	function removeAvatar() {
		avatarFile = null;
		avatarPreview = bot?.avatarUrl || null;
	}
</script>

<svelte:head>
	<title>Edit Agent Bot - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div class="flex items-center">
			<Button variant="ghost" size="sm" onclick={() => goto(`/app/super_admin/agent-bots/${botId}`)}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div class="ml-4">
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : `Edit ${formData.name || 'Agent Bot'}`}
				</h1>
				<p class="text-sm mt-1 text-muted-foreground">
					Agent Bots
				</p>
			</div>
		</div>
		<div class="flex items-center gap-2">
			<Button variant="destructive" onclick={handleDelete} disabled={loading || submitting}>
				<Trash2 class="h-4 w-4 mr-2" />
				Delete
			</Button>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		{#if loading}
			<Card class="max-w-2xl">
				<CardContent class="p-6">
					<div class="space-y-4">
						<div class="h-10 w-full animate-pulse rounded bg-muted"></div>
						<div class="h-10 w-full animate-pulse rounded bg-muted"></div>
						<div class="h-32 w-full animate-pulse rounded bg-muted"></div>
					</div>
				</CardContent>
			</Card>
		{:else}
			<Card class="max-w-2xl">
				<CardHeader>
					<CardTitle>Edit Bot Details</CardTitle>
					<CardDescription>Update your agent bot configuration</CardDescription>
				</CardHeader>
				<CardContent>
					<form onsubmit={handleSubmit} class="space-y-6">
						<!-- Avatar Upload -->
						<div class="space-y-2">
							<Label>Bot Avatar</Label>
							<div class="flex items-start gap-4">
								{#if avatarPreview}
									<div class="relative">
										<img 
											src={avatarPreview} 
											alt="Bot avatar" 
											class="h-32 w-32 rounded-lg object-cover border border-border"
										/>
										<button
											type="button"
											onclick={avatarFile ? removeAvatar : handleAvatarDelete}
											class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
										>
											<X class="h-3 w-3" />
										</button>
									</div>
								{:else}
									<div class="h-32 w-32 rounded-lg border-2 border-dashed border-border flex items-center justify-center bg-muted">
										<Upload class="h-8 w-8 text-muted-foreground" />
									</div>
								{/if}
								<div>
									<input
										type="file"
										accept="image/*"
										onchange={handleAvatarSelect}
										class="hidden"
										id="avatar-upload"
									/>
									<Label for="avatar-upload" class="cursor-pointer">
										<div class="px-4 py-2 bg-muted hover:bg-muted/80 rounded-lg border border-border text-sm font-medium text-foreground transition-colors">
											{avatarPreview ? 'Change Avatar' : 'Upload Avatar'}
										</div>
									</Label>
									<p class="text-xs text-muted-foreground mt-1">PNG, JPG up to 15MB</p>
								</div>
							</div>
						</div>

						<!-- Account Selection -->
						<div class="space-y-2">
							<Label for="account">Account</Label>
							{#if loadingAccounts}
								<div class="h-10 w-full animate-pulse rounded bg-muted"></div>
							{:else}
								<Select.Root type="single" name="account" value={selectedAccountId} onValueChange={(v) => selectedAccountId = v || '0'}>
									<Select.Trigger class="w-full">
										{accountTriggerContent}
									</Select.Trigger>
									<Select.Content>
										<Select.Item value="0">Global Bot (All Accounts)</Select.Item>
										{#each accounts as account (account.id)}
											<Select.Item value={String(account.id)}>{account.name}</Select.Item>
										{/each}
									</Select.Content>
								</Select.Root>
							{/if}
							<p class="text-xs text-muted-foreground">
								Choose an account or select global bot for all accounts
							</p>
						</div>

						<!-- Bot Name -->
						<div class="space-y-2">
							<Label for="name">Bot Name *</Label>
							<Input
								id="name"
								type="text"
								value={formData.name}
								oninput={(e) => formData.name = e.currentTarget.value}
								placeholder="Support Bot"
								disabled={submitting}
								class={errors.name ? 'border-destructive' : ''}
							/>
							{#if errors.name}
								<p class="text-sm text-destructive">{errors.name}</p>
							{/if}
						</div>

						<!-- Description -->
						<div class="space-y-2">
							<Label for="description">Description</Label>
							<Textarea
								id="description"
								value={formData.description}
								oninput={(e) => formData.description = e.currentTarget.value}
								placeholder="Automated support assistant"
								disabled={submitting}
								rows={3}
							/>
							<p class="text-xs text-muted-foreground">
								Brief description of what this bot does
							</p>
						</div>

						<!-- Outgoing URL -->
						<div class="space-y-2">
							<Label for="outgoingUrl">Outgoing URL</Label>
							<Input
								id="outgoingUrl"
								type="url"
								value={formData.outgoingUrl}
								oninput={(e) => formData.outgoingUrl = e.currentTarget.value}
								placeholder="https://your-bot-endpoint.com/webhook"
								disabled={submitting}
							/>
							<p class="text-xs text-muted-foreground">
								Webhook URL where bot messages will be sent
							</p>
						</div>

						<!-- Actions -->
						<div class="flex items-center space-x-2 pt-4">
							<Button type="submit" disabled={submitting}>
								<Save class="h-4 w-4 mr-2" />
								{submitting ? 'Saving...' : 'Save Changes'}
							</Button>
							<Button type="button" variant="outline" onclick={() => goto(`/app/super_admin/agent-bots/${botId}`)}>
								Cancel
							</Button>
						</div>
					</form>
				</CardContent>
			</Card>
		{/if}
	</section>
</div>