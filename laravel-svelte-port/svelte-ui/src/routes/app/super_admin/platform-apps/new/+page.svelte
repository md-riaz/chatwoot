<script lang="ts">
	import { goto } from '$app/navigation';
	import { api } from '$lib/api/superAdmin';
	import Button from '$lib/components/ui/button/button.svelte';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import { ArrowLeft, Plus } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';

	let creating = $state(false);

	let formData = $state({
		name: ''
	});

	let errors = $state<Record<string, string>>({});

	async function handleSubmit(e: Event) {
		e.preventDefault();
		errors = {};
		
		if (!formData.name.trim()) {
			toast.error('Please enter a platform app name');
			return;
		}

		creating = true;
		try {
			const platformApp = await api.platformApps.create({
				name: formData.name.trim()
			});

			toast.success('Platform app created successfully');
			goto(`/app/super_admin/platform-apps/${platformApp.id}`);
		} catch (error: any) {
			console.error('Failed to create platform app:', error);
			
			// Handle validation errors
			if (error.status === 422 && error.errors) {
				errors = error.errors;
			} else if (error.message) {
				toast.error(error.message);
			} else {
				toast.error('Failed to create platform app');
			}
		} finally {
			creating = false;
		}
	}
</script>

<svelte:head>
	<title>New Platform App - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center">
		<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/platform-apps')}>
			<ArrowLeft class="h-4 w-4" />
		</Button>
		<div class="ml-4">
			<h1 class="text-2xl font-semibold text-foreground">
				New Platform App
			</h1>
			<p class="text-sm mt-1 text-muted-foreground">
				Create a new platform app to enable API access for external applications
			</p>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		<Card class="max-w-2xl">
			<CardHeader>
				<CardTitle>Platform App Details</CardTitle>
				<CardDescription>Enter the details for the new platform app. An access token will be automatically generated.</CardDescription>
			</CardHeader>
			<CardContent>
				<form onsubmit={handleSubmit} class="space-y-6">
					<!-- Name -->
					<div class="space-y-2">
						<Label for="name">Name *</Label>
						<Input 
							id="name" 
							bind:value={formData.name} 
							placeholder="Enter platform app name"
							required 
							disabled={creating}
							class={errors.name ? 'border-destructive' : ''}
						/>
						{#if errors.name}
							<p class="text-sm text-destructive">{errors.name}</p>
						{/if}
						<p class="text-xs text-muted-foreground">
							Choose a descriptive name for your platform app (e.g., "Mobile App", "Integration Service")
						</p>
					</div>

					<!-- Form Actions -->
					<div class="flex items-center space-x-2 pt-4">
						<Button type="submit" disabled={creating}>
							<Plus class="h-4 w-4 mr-2" />
							{creating ? 'Creating...' : 'Create Platform App'}
						</Button>
						<Button 
							type="button" 
							variant="outline" 
							onclick={() => goto('/app/super_admin/platform-apps')} 
							disabled={creating}
						>
							Cancel
						</Button>
					</div>
				</form>
			</CardContent>
		</Card>
	</section>
</div>