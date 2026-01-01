<script lang="ts">
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/client';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { Card } from '$lib/components/ui/card';
	import { toast } from 'svelte-sonner';
	import { ArrowLeft, Plus } from 'lucide-svelte';
	
	let submitting = false;
	
	let formData = {
		name: '',
		locale: 'en'
	};
	
	let errors: Record<string, string> = {};
	
	async function handleSubmit() {
		errors = {};
		
		if (!formData.name) {
			errors.name = 'Name is required';
			return;
		}
		
		submitting = true;
		try {
			const account = await superAdminApi.createAccount(formData);
			toast.success('Account created successfully');
			goto(`/app/super_admin/accounts/${account.id}`);
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to create account');
			}
		} finally {
			submitting = false;
		}
	}
</script>

<svelte:head>
	<title>New Account - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-white dark:bg-slate-1 flex items-center" style="border-color: rgb(var(--slate-6));">
		<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/accounts')}>
			<ArrowLeft class="h-4 w-4" />
		</Button>
		<div class="ml-4">
			<h1 class="text-2xl font-semibold" style="color: rgb(var(--slate-12));">
				New Account
			</h1>
			<p class="text-sm mt-1" style="color: rgb(var(--slate-11));">
				Create a new account in your Chatwoot instance
			</p>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		<Card.Root class="max-w-2xl">
			<Card.Header>
				<Card.Title>Account Details</Card.Title>
				<Card.Description>Enter the details for the new account</Card.Description>
			</Card.Header>
			<Card.Content>
				<form on:submit|preventDefault={handleSubmit} class="space-y-4">
					<div class="space-y-2">
						<Label for="name">Account Name *</Label>
						<Input
							id="name"
							type="text"
							bind:value={formData.name}
							placeholder="Acme Inc."
							disabled={submitting}
							class={errors.name ? 'border-destructive' : ''}
							autofocus
						/>
						{#if errors.name}
							<p class="text-sm text-destructive">{errors.name}</p>
						{/if}
					</div>
					
					<div class="space-y-2">
						<Label for="locale">Locale</Label>
						<Input
							id="locale"
							type="text"
							bind:value={formData.locale}
							placeholder="en"
							disabled={submitting}
						/>
						<p class="text-xs" style="color: rgb(var(--slate-10));">
							Default language for this account
						</p>
					</div>
					
					<div class="flex items-center space-x-2 pt-4">
						<Button type="submit" disabled={submitting}>
							<Plus class="h-4 w-4 mr-2" />
							{submitting ? 'Creating...' : 'Create Account'}
						</Button>
						<Button type="button" variant="outline" onclick={() => goto('/app/super_admin/accounts')}>
							Cancel
						</Button>
					</div>
				</form>
			</Card.Content>
		</Card.Root>
	</section>
</div>
