<script lang="ts">
	import { goto } from '$app/navigation';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Textarea from '$lib/components/ui/textarea/textarea.svelte';
	import { ChevronLeft, Save, Upload, X } from 'lucide-svelte';
	import { api } from '$lib/api/superAdmin';
	import { toast } from 'svelte-sonner';

	let saving = false;
	let avatarFile: File | null = null;
	let avatarPreview: string | null = null;

	let formData = {
		name: '',
		description: '',
		outgoing_url: ''
	};

	async function handleSave() {
		if (!formData.name.trim()) {
			toast.error('Bot name is required');
			return;
		}

		saving = true;
		try {
			const newBot = await api.agentBots.create(formData);

			if (avatarFile && newBot.id) {
				await api.agentBots.uploadAvatar(newBot.id, avatarFile);
			}

			toast.success('Agent bot created successfully');
			goto(`/app/super_admin/agent-bots/${newBot.id}`);
		} catch (error: any) {
			toast.error('Failed to create agent bot: ' + (error.message || 'Unknown error'));
		} finally {
			saving = false;
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
					<h1 class="text-2xl font-semibold text-slate-12">Create New Agent Bot</h1>
				</div>
			</div>
			<Button on:click={handleSave} disabled={saving} class="bg-iris-9 text-white hover:bg-iris-10">
				<Save class="mr-2 h-4 w-4" />
				{saving ? 'Creating...' : 'Create Bot'}
			</Button>
		</div>
	</div>

	<!-- Content -->
	<div class="flex-1 overflow-auto bg-white p-8 dark:bg-slate-1">
		<div class="mx-auto max-w-2xl space-y-6">
			<!-- Avatar -->
			<div class="space-y-2">
				<Label>Avatar</Label>
				<div class="flex items-center gap-4">
					{#if avatarPreview}
						<div class="relative">
							<img src={avatarPreview} alt="Bot avatar" class="h-24 w-24 rounded-full object-cover" />
							<button
								on:click={removeAvatar}
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
				<Input id="name" bind:value={formData.name} required autofocus />
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
	</div>
</div>
