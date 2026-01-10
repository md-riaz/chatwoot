<script lang="ts">
	import { goto } from '$app/navigation';
	import { api } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import * as Select from '$lib/components/ui/select';
	import { Textarea } from '$lib/components/ui/textarea';
	import { ArrowLeft, Plus, Upload, X } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	let submitting = $state(false);
	let loadingAccounts = $state(true);
	let avatarFile = $state<File | null>(null);
	let avatarPreview = $state<string | null>(null);
	let accounts = $state<any[]>([]);

	let formData = $state({
		name: '',
		description: '',
		outgoing_url: '',
		account_id: 0 as number // 0 = global bot, >0 = specific account
	});

	// Convert account_id to string for Select component
	let selectedAccountId = $state<string>('0');

	let errors = $state<Record<string, string>>({});

	// Update formData when selectedAccountId changes
	$effect(() => {
		formData.account_id = Number(selectedAccountId);
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
		await loadAccounts();
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

	async function handleSubmit(e: Event) {
		e.preventDefault();
		errors = {};

		if (!formData.name.trim()) {
			errors.name = 'Bot name is required';
			return;
		}

		submitting = true;
		try {
			const newBot = await api.agentBots.create(formData);

			if (avatarFile && newBot.id) {
				await api.agentBots.uploadAvatar(String(newBot.id), avatarFile);
			}

			toast.success('Agent bot created successfully');
			goto(`/app/super_admin/agent-bots/${newBot.id}`);
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to create agent bot');
			}
		} finally {
			submitting = false;
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

	function removeAvatar() {
		avatarFile = null;
		avatarPreview = null;
	}
</script>

<svelte:head>
	<title>New Agent Bot - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center">
		<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/agent-bots')}>
			<ArrowLeft class="h-4 w-4" />
		</Button>
		<div class="ml-4">
			<h1 class="text-2xl font-semibold text-foreground">
				New Agent Bot
			</h1>
			<p class="text-sm mt-1 text-muted-foreground">
				Create a new agent bot for automated responses
			</p>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		<Card class="max-w-2xl">
			<CardHeader>
				<CardTitle>Bot Details</CardTitle>
				<CardDescription>Configure your new agent bot</CardDescription>
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
										onclick={removeAvatar}
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
										Upload Avatar
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
							<Select.Root type="single" name="account" bind:value={selectedAccountId}>
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
							bind:value={formData.name}
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
							bind:value={formData.description}
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
						<Label for="outgoing_url">Outgoing URL</Label>
						<Input
							id="outgoing_url"
							type="url"
							bind:value={formData.outgoing_url}
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
							<Plus class="h-4 w-4 mr-2" />
							{submitting ? 'Creating...' : 'Create Bot'}
						</Button>
						<Button type="button" variant="outline" onclick={() => goto('/app/super_admin/agent-bots')}>
							Cancel
						</Button>
					</div>
				</form>
			</CardContent>
		</Card>
	</section>
</div>