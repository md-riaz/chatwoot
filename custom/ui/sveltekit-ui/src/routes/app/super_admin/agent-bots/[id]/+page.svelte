<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Textarea from '$lib/components/ui/textarea/textarea.svelte';
	import { ChevronLeft, Save, Trash2, Upload, X } from 'lucide-svelte';
	import { api } from '$lib/api/client';
	import { toast } from 'svelte-sonner';

	const botId = $page.params.id;

	let bot: any = null;
	let loading = true;
	let saving = false;
	let avatarFile: File | null = null;
	let avatarPreview: string | null = null;

	let formData = {
		name: '',
		description: '',
		outgoing_url: ''
	};

	async function loadBot() {
		loading = true;
		try {
			bot = await api.agentBots.get(botId);
			formData = {
				name: bot.name || '',
				description: bot.description || '',
				outgoing_url: bot.outgoing_url || ''
			};
			if (bot.avatar_url) {
				avatarPreview = bot.avatar_url;
			}
		} catch (error: any) {
			toast.error('Failed to load agent bot: ' + (error.message || 'Unknown error'));
		} finally {
			loading = false;
		}
	}

	async function handleSave() {
		if (!formData.name.trim()) {
			toast.error('Bot name is required');
			return;
		}

		saving = true;
		try {
			await api.agentBots.update(botId, formData);

			if (avatarFile) {
				await api.agentBots.uploadAvatar(botId, avatarFile);
			}

			toast.success('Agent bot updated successfully');
			goto('/app/super_admin/agent-bots');
		} catch (error: any) {
			toast.error('Failed to update agent bot: ' + (error.message || 'Unknown error'));
		} finally {
			saving = false;
		}
	}

	async function handleDelete() {
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

	onMount(() => {
		loadBot();
	});
</script>

<div class="flex h-full flex-col">
	<!-- Header -->
	<div class="border-b border-slate-6 bg-white px-8 py-6 dark:bg-slate-1">
		<div class="flex items-center justify-between">
			<div class="flex items-center gap-4">
				<Button variant="ghost" size="sm" on:click={() => goto('/app/super_admin/agent-bots')}>
					<ChevronLeft class="h-4 w-4" />
				</Button>
				<div>
					<p class="text-sm text-slate-11">Agent Bots</p>
					<h1 class="text-2xl font-semibold text-slate-12">
						{loading ? 'Loading...' : formData.name || 'Edit Agent Bot'}
					</h1>
				</div>
			</div>
			<div class="flex gap-2">
				<Button variant="destructive" on:click={handleDelete} disabled={loading}>
					<Trash2 class="mr-2 h-4 w-4" />
					Delete
				</Button>
				<Button on:click={handleSave} disabled={loading || saving} class="bg-iris-9 text-white hover:bg-iris-10">
					<Save class="mr-2 h-4 w-4" />
					{saving ? 'Saving...' : 'Save Changes'}
				</Button>
			</div>
		</div>
	</div>

	<!-- Content -->
	<div class="flex-1 overflow-auto bg-white p-8 dark:bg-slate-1">
		{#if loading}
			<div class="space-y-4">
				<div class="h-10 w-full animate-pulse rounded bg-slate-3"></div>
				<div class="h-10 w-full animate-pulse rounded bg-slate-3"></div>
				<div class="h-32 w-full animate-pulse rounded bg-slate-3"></div>
			</div>
		{:else}
			<div class="mx-auto max-w-2xl space-y-6">
				<!-- Avatar -->
				<div class="space-y-2">
					<Label>Avatar</Label>
					<div class="flex items-center gap-4">
						{#if avatarPreview}
							<div class="relative">
								<img src={avatarPreview} alt="Bot avatar" class="h-24 w-24 rounded-full object-cover" />
								<button
									on:click={handleAvatarDelete}
									class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
								>
									<X class="h-4 w-4" />
								</button>
							</div>
						{/if}
						<label
							class="flex cursor-pointer items-center gap-2 rounded-md border border-slate-6 px-4 py-2 text-sm font-medium text-slate-12 hover:bg-slate-2"
						>
							<Upload class="h-4 w-4" />
							Upload Avatar
							<input type="file" accept="image/*" on:change={handleAvatarSelect} class="hidden" />
						</label>
					</div>
				</div>

				<!-- Name -->
				<div class="space-y-2">
					<Label for="name">Name *</Label>
					<Input id="name" bind:value={formData.name} required />
				</div>

				<!-- Description -->
				<div class="space-y-2">
					<Label for="description">Description</Label>
					<Textarea id="description" bind:value={formData.description} rows={4} />
				</div>

				<!-- Outgoing URL -->
				<div class="space-y-2">
					<Label for="outgoing_url">Outgoing URL</Label>
					<Input id="outgoing_url" type="url" bind:value={formData.outgoing_url} />
				</div>
			</div>
		{/if}
	</div>
</div>
