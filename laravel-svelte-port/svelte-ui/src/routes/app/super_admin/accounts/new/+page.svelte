<script lang="ts">
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { ArrowLeft, Plus } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';
	
	let submitting = $state(false);
	
	let formData = $state({
		name: '',
		locale: 'en',
		limits: {
			agents: '',
			inboxes: ''
		}
	});
	
	let errors = $state<Record<string, string>>({});
	
	async function handleSubmit(e: Event) {
		e.preventDefault();
		errors = {};
		
		if (!formData.name) {
			errors.name = 'Name is required';
			return;
		}
		
		submitting = true;
		try {
			const account = await superAdminApi.createAccount({
				...formData,
				limits: {
					agents: formData.limits.agents ? Number(formData.limits.agents) : null,
					inboxes: formData.limits.inboxes ? Number(formData.limits.inboxes) : null
				}
			});
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
	<header class="px-8 py-6 border-b bg-card flex items-center">
		<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/accounts')}>
			<ArrowLeft class="h-4 w-4" />
		</Button>
		<div class="ml-4">
			<h1 class="text-2xl font-semibold text-foreground">
				New Account
			</h1>
			<p class="text-sm mt-1 text-muted-foreground">
				Create a new account in your Chatwoot instance
			</p>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		   <Card class="max-w-2xl">
			   <CardHeader>
				   <CardTitle>Account Details</CardTitle>
				   <CardDescription>Enter the details for the new account</CardDescription>
			   </CardHeader>
			   <CardContent>
				<form onsubmit={handleSubmit} class="space-y-4">
					<div class="space-y-2">
						<Label for="name">Account Name *</Label>
						<Input
							id="name"
							type="text"
							bind:value={formData.name}
							placeholder="Acme Inc."
							disabled={submitting}
							class={errors.name ? 'border-destructive' : ''}
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
						<p class="text-xs text-muted-foreground">
							Default language for this account
						</p>
					</div>
					
					<!-- Account Limits -->
					<div class="space-y-4 pt-4 border-t">
						<div>
							<h3 class="text-sm font-medium text-foreground">Account Limits</h3>
							<p class="text-xs text-muted-foreground">Set usage limits for this account (optional)</p>
						</div>
						
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div class="space-y-2">
								<Label for="agentLimit">Agent Limit</Label>
								<Input
									id="agentLimit"
									type="number"
									bind:value={formData.limits.agents}
									placeholder="Unlimited"
									disabled={submitting}
									min="0"
								/>
								<p class="text-xs text-muted-foreground">
									Maximum number of agents allowed
								</p>
							</div>
							
							<div class="space-y-2">
								<Label for="inboxLimit">Inbox Limit</Label>
								<Input
									id="inboxLimit"
									type="number"
									bind:value={formData.limits.inboxes}
									placeholder="Unlimited"
									disabled={submitting}
									min="0"
								/>
								<p class="text-xs text-muted-foreground">
									Maximum number of inboxes allowed
								</p>
							</div>
						</div>
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
			   </CardContent>
		   </Card>
	</section>
</div>
