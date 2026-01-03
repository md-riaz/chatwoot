<script lang="ts">
	import { goto } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import { ChevronLeft, Save, Upload } from 'lucide-svelte';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Select from '$lib/components/ui/select/select-native.svelte';
	import { superAdminApi } from '$lib/api/superAdmin';

	let creating = false;
	let avatarFile: File | null = null;
	let avatarPreview: string | null = null;

	let formData = {
		name: '',
		display_name: '',
		email: '',
		role: 'agent',
		password: ''
	};

	async function handleCreate() {
		if (!formData.name || !formData.email || !formData.password) {
			toast.error('Please fill in all required fields');
			return;
		}

		creating = true;
		try {
			const user = await superAdminApi.users.create(formData);

			// Upload avatar if selected
			if (avatarFile && user.id) {
				await superAdminApi.users.uploadAvatar(user.id, avatarFile);
			}

			toast.success('User created successfully');
			goto(`/app/super_admin/users/${user.id}`);
		} catch (error: any) {
			toast.error(error.message || 'Failed to create user');
			console.error(error);
		} finally {
			creating = false;
		}
	}

	function handleFileSelect(e: Event) {
		const target = e.target as HTMLInputElement;
		const file = target.files?.[0];
		if (file) {
			avatarFile = file;
			const reader = new FileReader();
			reader.onload = (e) => {
				avatarPreview = e.target?.result as string;
			};
			reader.readAsDataURL(file);
		}
	}
</script>

<div class="h-full flex flex-col bg-white dark:bg-slate-1">
	<div class="flex items-center justify-between px-8 py-6 border-b border-slate-6">
		<div class="flex items-center gap-4">
			<Button variant="ghost" size="icon" onclick={() => goto('/app/super_admin/users')}>
				<ChevronLeft class="h-5 w-5" />
			</Button>
			<div>
				<div class="text-xs text-slate-11 mb-1">Users / New</div>
				<h1 class="text-2xl font-semibold text-slate-12">Create New User</h1>
			</div>
		</div>
		<Button onclick={handleCreate} disabled={creating}>
			<Save class="h-4 w-4 mr-2" />
			{creating ? 'Creating...' : 'Create User'}
		</Button>
	</div>

	<div class="flex-1 overflow-auto p-8">
		<div class="max-w-2xl space-y-6">
			<!-- Avatar Section -->
			<div class="space-y-2">
				<Label>Avatar</Label>
				<div class="flex items-center gap-4">
					{#if avatarPreview}
						<img
							src={avatarPreview}
							alt="User avatar preview"
							class="h-32 w-32 rounded-lg object-cover border border-slate-6"
						/>
					{:else}
						<div class="h-32 w-32 rounded-lg border-2 border-dashed border-slate-6 flex items-center justify-center bg-slate-2">
							<Upload class="h-8 w-8 text-slate-9" />
						</div>
					{/if}
					<div>
						<input
							type="file"
							accept="image/*"
							onchange={handleFileSelect}
							class="hidden"
							id="avatar-upload"
						/>
						<Label for="avatar-upload" class="cursor-pointer">
							<div class="px-4 py-2 bg-slate-2 hover:bg-slate-3 rounded-lg border border-slate-6 text-sm font-medium text-slate-12 transition">
								Upload Avatar
							</div>
						</Label>
						<p class="text-xs text-slate-10 mt-1">PNG, JPG up to 2MB</p>
					</div>
				</div>
			</div>

			<!-- Name -->
			<div class="space-y-2">
				<Label for="name">Name *</Label>
				<Input id="name" bind:value={formData.name} required autofocus />
			</div>

			<!-- Display Name -->
			<div class="space-y-2">
				<Label for="display_name">Display Name</Label>
				<Input id="display_name" bind:value={formData.display_name} />
			</div>

			<!-- Email -->
			<div class="space-y-2">
				<Label for="email">Email *</Label>
				<Input id="email" type="email" bind:value={formData.email} required />
			</div>

			<!-- Role -->
			<div class="space-y-2">
				<Label for="role">Role *</Label>
				<Select id="role" bind:value={formData.role}>
					<option value="administrator">Administrator</option>
					<option value="agent">Agent</option>
				</Select>
			</div>

			<!-- Password -->
			<div class="space-y-2">
				<Label for="password">Password *</Label>
				<Input id="password" type="password" bind:value={formData.password} required />
				<p class="text-xs text-slate-10">Minimum 6 characters</p>
			</div>
		</div>
	</div>
</div>
