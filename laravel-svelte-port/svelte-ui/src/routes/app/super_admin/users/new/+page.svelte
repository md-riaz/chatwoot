<script lang="ts">
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { ArrowLeft, Plus, Upload } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';

	let creating = $state(false);
	let avatarFile: File | null = null;
	let avatarPreview = $state<string | null>(null);

	let formData = $state({
		name: '',
		displayName: '',
		email: '',
		role: 'agent',
		password: ''
	});

	let errors = $state<Record<string, string>>({});

	async function handleSubmit(e: Event) {
		e.preventDefault();
		errors = {};
		
		if (!formData.name || !formData.email || !formData.password) {
			toast.error('Please fill in all required fields');
			return;
		}

		creating = true;
		try {
			const userData = {
				name: formData.name,
				displayName: formData.displayName,
				email: formData.email,
				role: formData.role,
				password: formData.password
			};

			const user = await superAdminApi.createUser(userData);

			// Upload avatar if selected
			if (avatarFile && user.id) {
				await superAdminApi.uploadUserAvatar(user.id, avatarFile);
			}

			toast.success('User created successfully');
			goto(`/app/super_admin/users/${user.id}`);
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to create user');
			}
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

<svelte:head>
	<title>New User - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center">
		<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/users')}>
			<ArrowLeft class="h-4 w-4" />
		</Button>
		<div class="ml-4">
			<h1 class="text-2xl font-semibold text-foreground">
				New User
			</h1>
			<p class="text-sm mt-1 text-muted-foreground">
				Create a new user in your Chatwoot instance
			</p>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		<Card class="max-w-2xl">
			<CardHeader>
				<CardTitle>User Details</CardTitle>
				<CardDescription>Enter the details for the new user</CardDescription>
			</CardHeader>
			<CardContent>
				<form onsubmit={handleSubmit} class="space-y-6">
					<!-- Avatar Section -->
					<div class="space-y-2">
						<Label>Avatar</Label>
						<div class="flex items-center gap-4">
							{#if avatarPreview}
								<img
									src={avatarPreview}
									alt="User avatar preview"
									class="h-32 w-32 rounded-lg object-cover border border-border"
								/>
							{:else}
								<div class="h-32 w-32 rounded-lg border-2 border-dashed border-border flex items-center justify-center bg-muted">
									<Upload class="h-8 w-8 text-muted-foreground" />
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
									<div class="px-4 py-2 bg-muted hover:bg-muted/80 rounded-lg border border-border text-sm font-medium text-foreground transition-colors">
										Upload Avatar
									</div>
								</Label>
								<p class="text-xs text-muted-foreground mt-1">PNG, JPG up to 2MB</p>
							</div>
						</div>
					</div>

					<!-- Name -->
					<div class="space-y-2">
						<Label for="name">Name *</Label>
						<Input 
							id="name" 
							bind:value={formData.name} 
							required 
							disabled={creating}
							class={errors.name ? 'border-destructive' : ''}
						/>
						{#if errors.name}
							<p class="text-sm text-destructive">{errors.name}</p>
						{/if}
					</div>

					<!-- Display Name -->
					<div class="space-y-2">
						<Label for="displayName">Display Name</Label>
						<Input 
							id="displayName" 
							bind:value={formData.displayName} 
							disabled={creating}
							class={errors.displayName ? 'border-destructive' : ''}
						/>
						{#if errors.displayName}
							<p class="text-sm text-destructive">{errors.displayName}</p>
						{/if}
					</div>

					<!-- Email -->
					<div class="space-y-2">
						<Label for="email">Email *</Label>
						<Input 
							id="email" 
							type="email" 
							bind:value={formData.email} 
							required 
							disabled={creating}
							class={errors.email ? 'border-destructive' : ''}
						/>
						{#if errors.email}
							<p class="text-sm text-destructive">{errors.email}</p>
						{/if}
					</div>

					<!-- Role -->
					<div class="space-y-2">
						<Label for="role">Role *</Label>
						<select 
							id="role"
							bind:value={formData.role}
							disabled={creating}
							class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
						>
							<option value="agent">Agent</option>
							<option value="administrator">Administrator</option>
						</select>
						{#if errors.role}
							<p class="text-sm text-destructive">{errors.role}</p>
						{/if}
					</div>

					<!-- Password -->
					<div class="space-y-2">
						<Label for="password">Password *</Label>
						<Input 
							id="password" 
							type="password" 
							bind:value={formData.password} 
							required 
							disabled={creating}
							class={errors.password ? 'border-destructive' : ''}
						/>
						{#if errors.password}
							<p class="text-sm text-destructive">{errors.password}</p>
						{/if}
						<p class="text-xs text-muted-foreground">Minimum 6 characters</p>
					</div>

					<!-- Form Actions -->
					<div class="flex items-center space-x-2 pt-4">
						<Button type="submit" disabled={creating}>
							<Plus class="h-4 w-4 mr-2" />
							{creating ? 'Creating...' : 'Create User'}
						</Button>
						<Button type="button" variant="outline" onclick={() => goto('/app/super_admin/users')} disabled={creating}>
							Cancel
						</Button>
					</div>
				</form>
			</CardContent>
		</Card>
	</section>
</div>