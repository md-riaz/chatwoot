<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { superAdminApi } from '$lib/api/superAdmin';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Select from '$lib/components/ui/select/select-native.svelte';
	import Skeleton from '$lib/components/ui/skeleton/skeleton.svelte';
	import { ChevronLeft, Lock, Mail, Save, Trash2, Unlock, Upload, X } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	let userId = $page.params.id;
	let user: any = null;
	let loading = true;
	let saving = false;
	let avatarFile: File | null = null;
	let avatarPreview: string | null = null;

	let formData = {
		name: '',
		display_name: '',
		email: '',
		role: 'agent',
		password: ''
	};

	async function loadUser() {
		loading = true;
		try {
			user = await superAdminApi.getUser(parseInt(userId));
			formData = {
				name: user.name || '',
				display_name: user.displayName || '',
				email: user.email || '',
				role: user.role || 'agent',
				password: ''
			};
			if (user.avatarUrl) {
				avatarPreview = user.avatarUrl;
			}
		} catch (error) {
			toast.error('Failed to load user');
			console.error(error);
		} finally {
			loading = false;
		}
	}

	async function handleSave() {
		saving = true;
		try {
			// Update user info
			await superAdminApi.updateUser(parseInt(userId), formData);

			// Upload avatar if selected
			if (avatarFile) {
				await superAdminApi.uploadUserAvatar(parseInt(userId), avatarFile);
			}

			toast.success('User updated successfully');
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to update user');
			console.error(error);
		} finally {
			saving = false;
		}
	}

	async function handleDelete() {
		if (!confirm('Are you sure you want to delete this user?')) return;

		try {
			await superAdminApi.deleteUser(parseInt(userId));
			toast.success('User deleted successfully');
			goto('/app/super_admin/users');
		} catch (error: any) {
			toast.error(error.message || 'Failed to delete user');
			console.error(error);
		}
	}

	async function handleConfirmEmail() {
		try {
			await superAdminApi.confirmUserEmail(parseInt(userId));
			toast.success('Email confirmed successfully');
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to confirm email');
			console.error(error);
		}
	}

	async function handleToggleLock() {
		try {
			if (isUserLocked()) {
				await superAdminApi.unlockUser(parseInt(userId));
				toast.success('User unlocked successfully');
			} else {
				await superAdminApi.lockUser(parseInt(userId));
				toast.success('User locked successfully');
			}
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to update user status');
			console.error(error);
		}
	}

	async function handleDeleteAvatar() {
		if (!confirm('Are you sure you want to delete this avatar?')) return;

		try {
			await superAdminApi.deleteUserAvatar(parseInt(userId));
			avatarPreview = null;
			avatarFile = null;
			toast.success('Avatar deleted successfully');
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to delete avatar');
			console.error(error);
		}
	}
	
	function isUserConfirmed(): boolean {
		return !!user?.emailVerifiedAt;
	}
	
	function isUserLocked(): boolean {
		return user?.customAttributes?.locked === true;
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

	onMount(() => {
		loadUser();
	});
</script>

<div class="h-full flex flex-col bg-white dark:bg-slate-1">
	<div class="flex items-center justify-between px-8 py-6 border-b border-slate-6">
		<div class="flex items-center gap-4">
			<Button variant="ghost" size="icon" onclick={() => goto('/app/super_admin/users')}>
				<ChevronLeft class="h-5 w-5" />
			</Button>
			<div>
				<div class="text-xs text-slate-11 mb-1">Users / Edit</div>
				<h1 class="text-2xl font-semibold text-slate-12">
					{loading ? 'Loading...' : user?.name || 'User Details'}
				</h1>
			</div>
		</div>
		<div class="flex items-center gap-3">
			{#if !loading && user}
				{#if !isUserConfirmed()}
					<Button variant="outline" onclick={handleConfirmEmail}>
						<Mail class="h-4 w-4 mr-2" />
						Confirm Email
					</Button>
				{/if}
				<Button
					variant="outline"
					onclick={handleToggleLock}
				>
					{#if isUserLocked()}
						<Unlock class="h-4 w-4 mr-2" />
						Unlock User
					{:else}
						<Lock class="h-4 w-4 mr-2" />
						Lock User
					{/if}
				</Button>
				<Button variant="destructive" onclick={handleDelete}>
					<Trash2 class="h-4 w-4 mr-2" />
					Delete
				</Button>
			{/if}
			<Button onclick={handleSave} disabled={saving || loading}>
				<Save class="h-4 w-4 mr-2" />
				{saving ? 'Saving...' : 'Save'}
			</Button>
		</div>
	</div>

	<div class="flex-1 overflow-auto p-8">
		{#if loading}
			<div class="max-w-2xl space-y-6">
				<Skeleton class="h-32 w-32 rounded-lg" />
				<Skeleton class="h-10 w-full" />
				<Skeleton class="h-10 w-full" />
				<Skeleton class="h-10 w-full" />
			</div>
		{:else}
			<div class="max-w-2xl space-y-6">
				<!-- Avatar Section -->
				<div class="space-y-2">
					<Label>Avatar</Label>
					<div class="flex items-center gap-4">
						{#if avatarPreview}
							<div class="relative">
								<img
									src={avatarPreview}
									alt="User avatar"
									class="h-32 w-32 rounded-lg object-cover border border-slate-6"
								/>
								<button
									type="button"
									onclick={handleDeleteAvatar}
									class="absolute -top-2 -right-2 p-1 bg-ruby-9 text-white rounded-full hover:bg-ruby-10 transition"
								>
									<X class="h-4 w-4" />
								</button>
							</div>
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
									{avatarPreview ? 'Change Avatar' : 'Upload Avatar'}
								</div>
							</Label>
							<p class="text-xs text-slate-10 mt-1">PNG, JPG up to 2MB</p>
						</div>
					</div>
				</div>

				<!-- Name -->
				<div class="space-y-2">
					<Label for="name">Name *</Label>
					<Input id="name" bind:value={formData.name} required />
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
					<Label for="password">Password (leave blank to keep unchanged)</Label>
					<Input id="password" type="password" bind:value={formData.password} />
				</div>
			</div>
		{/if}
	</div>
</div>
