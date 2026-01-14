<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { superAdminApi } from '$lib/api/superAdmin';
	import FeatureFlagManager from '$lib/components/FeatureFlagManager.svelte';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import * as Select from '$lib/components/ui/select';
	import { ArrowLeft, Save } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';
	
	let loading = $state(true);
	let submitting = $state(false);
	let accountId: number = Number($page.params.id);

	let formData = $state({
		name: '',
		status: 'active',
		locale: 'en',
		domain: '',
		supportEmail: '',
		autoResolveDuration: '',
		selectedFeatureFlags: [] as string[],
		allFeatures: {} as Record<string, {
			available: boolean;
			display_name?: string;
			displayName?: string;
			enabled: boolean;
			premium: boolean;
			help_url?: string;
			helpUrl?: string;
		}>,
		settings: {} as Record<string, any>,
		limits: {
			agents: '',
			inboxes: ''
		} as Record<string, any>,
		customAttributes: {} as Record<string, any>
	});

	let errors = $state<Record<string, string>>({});
	
	// Status display for Select component
	const statusDisplay = $derived(
		formData.status === 'active' ? 'Active' : 
		formData.status === 'suspended' ? 'Suspended' : 
		'Select status'
	);
	
	async function loadAccount() {
		loading = true;
		try {
			   const account = await superAdminApi.getAccount(accountId);
			   formData = {
				   name: account.name || '',
				   status: account.status || 'active',
				   locale: account.locale || 'en',
				   domain: account.domain || '',
				   supportEmail: account.supportEmail || '',
				   autoResolveDuration: String(account.autoResolveDuration ?? ''),
				   selectedFeatureFlags: account.selectedFeatureFlags || [],
				   allFeatures: account.allFeatures || {},
				   settings: account.settings || {},
				   limits: {
					   agents: String(account.limits?.agents ?? ''),
					   inboxes: String(account.limits?.inboxes ?? ''),
					   ...account.limits
				   },
				   customAttributes: account.customAttributes || {}
			   };
		} catch (error: any) {
			toast.error(error.message || 'Failed to load account');
			goto('/app/super_admin/accounts');
		} finally {
			loading = false;
		}
	}
	
	async function handleSubmit(event: Event) {
	   event.preventDefault();
	   errors = {};
   
	   if (!formData.name) {
		   errors.name = 'Name is required';
		   return;
	   }
   
	   submitting = true;
		try {
			   await superAdminApi.updateAccount(accountId, {
				   name: formData.name,
				   status: formData.status as 'active' | 'suspended' | undefined,
				   locale: formData.locale,
				   domain: formData.domain,
				   supportEmail: formData.supportEmail,
				   autoResolveDuration: formData.autoResolveDuration ? Number(formData.autoResolveDuration) : undefined,
				   selectedFeatureFlags: formData.selectedFeatureFlags, // Send whatever format the component provides
				   settings: formData.settings,
				   limits: {
					   agents: formData.limits.agents ? Number(formData.limits.agents) : null,
					   inboxes: formData.limits.inboxes ? Number(formData.limits.inboxes) : null,
					   // Include any other limits that might exist
					   ...Object.fromEntries(
						   Object.entries(formData.limits)
							   .filter(([key]) => !['agents', 'inboxes'].includes(key))
							   .map(([key, value]) => [key, value ? Number(value) : null])
					   )
				   },
				   customAttributes: formData.customAttributes
			   });
			toast.success('Account updated successfully');
			goto(`/app/super_admin/accounts/${accountId}`);
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to update account');
			}
		} finally {
			submitting = false;
		}
	}
	
	   function handleFeaturesChange(features: string[] | Record<string, boolean>) {
		   // Convert to string[] format for storage
		   if (Array.isArray(features)) {
			   formData.selectedFeatureFlags = features;
		   } else {
			   // Convert Record<string, boolean> to string[] (keys where value is true)
			   formData.selectedFeatureFlags = Object.keys(features).filter(key => features[key]);
		   }
	   }
	   
	   onMount(() => {
		   loadAccount();
	   });
</script>

<svelte:head>
	<title>Edit Account - Super Admin - Chatwoot</title>
</svelte:head>

<div class="h-full flex flex-col bg-background">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div class="flex items-center space-x-4">
			<Button variant="ghost" size="sm" onclick={() => goto(`/app/super_admin/accounts/${accountId}`)}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div>
				<h1 class="text-2xl font-semibold text-foreground">
					Edit Account
				</h1>
				<p class="text-sm text-muted-foreground mt-1">
					Account ID: {accountId}
				</p>
			</div>
		</div>
	</header>

	<!-- Main Content -->
	<main class="flex-1 overflow-auto bg-background p-8">
		{#if loading}
			<div class="bg-card rounded-lg shadow-sm p-8">
				<div class="animate-pulse space-y-4">
					<div class="h-4 bg-muted rounded w-1/4"></div>
					<div class="h-4 bg-muted rounded w-1/2"></div>
					<div class="h-4 bg-muted rounded w-1/3"></div>
				</div>
			</div>
		{:else}
			<form onsubmit={handleSubmit} class="space-y-6">
				<!-- Account Information Section -->
				<section class="bg-card rounded-lg shadow-sm">
					<div class="px-6 py-4 border-b border-border">
						<h2 class="text-lg font-medium text-foreground">Account Information</h2>
						<p class="text-sm text-muted-foreground mt-1">Update basic account details</p>
					</div>
					<div class="p-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div>
									<Label for="name">Account Name *</Label>
									<Input
										id="name"
										type="text"
										bind:value={formData.name}
										disabled={submitting}
										class={errors.name ? 'border-red-500' : ''}
										required
									/>
									{#if errors.name}
										<p class="text-sm text-red-600 dark:text-red-400 mt-1">{errors.name}</p>
									{/if}
								</div>
								
								<div>
									<Label for="status">Status</Label>
									<Select.Root type="single" bind:value={formData.status} disabled={submitting}>
										<Select.Trigger class="w-full">
											{statusDisplay}
										</Select.Trigger>
										<Select.Content>
											<Select.Item value="active">Active</Select.Item>
											<Select.Item value="suspended">Suspended</Select.Item>
										</Select.Content>
									</Select.Root>
								</div>
								
								<div>
									<Label for="locale">Locale</Label>
									<Input
										id="locale"
										type="text"
										bind:value={formData.locale}
										placeholder="en"
										disabled={submitting}
									/>
								</div>
							</div>
							<div class="space-y-4">
								<div>
									<Label for="domain">Domain</Label>
									<Input
										id="domain"
										type="text"
										bind:value={formData.domain}
										placeholder="example.com"
										disabled={submitting}
									/>
								</div>
								
								<div>
									<Label for="supportEmail">Support Email</Label>
									<Input
										id="supportEmail"
										type="email"
										bind:value={formData.supportEmail}
										placeholder="support@example.com"
										disabled={submitting}
									/>
								</div>
								
								<div>
									<Label for="autoResolveDuration">Auto Resolve Duration (days)</Label>
									<Input
										id="autoResolveDuration"
										type="number"
										bind:value={formData.autoResolveDuration}
										placeholder="30"
										disabled={submitting}
									/>
								</div>
							</div>
						</div>
					</div>
				</section>

				<!-- Feature Flags Section -->
				<section class="bg-card rounded-lg shadow-sm">
					<div class="px-6 py-4 border-b border-border">
						<h2 class="text-lg font-medium text-foreground">Feature Flags</h2>
						<p class="text-sm text-muted-foreground mt-1">Enable or disable features for this account</p>
					</div>
					<div class="p-6">
						<FeatureFlagManager
							selectedFeatures={formData.selectedFeatureFlags}
							allFeatures={formData.allFeatures}
							onFeaturesChange={handleFeaturesChange}
							disabled={submitting}
						/>
					</div>
				</section>

				<!-- Account Limits Section -->
				<section class="bg-card rounded-lg shadow-sm">
					<div class="px-6 py-4 border-b border-border">
						<h2 class="text-lg font-medium text-foreground">Account Limits</h2>
						<p class="text-sm text-muted-foreground mt-1">Set usage limits for this account</p>
					</div>
					<div class="p-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div>
								<Label for="agentLimit">Agent Limit</Label>
								<Input
									id="agentLimit"
									type="number"
									bind:value={formData.limits.agents}
									placeholder="Unlimited"
									disabled={submitting}
									min="0"
								/>
								<p class="text-sm text-muted-foreground mt-1">
									Maximum number of agents allowed. Leave empty for unlimited.
								</p>
							</div>
							
							<div>
								<Label for="inboxLimit">Inbox Limit</Label>
								<Input
									id="inboxLimit"
									type="number"
									bind:value={formData.limits.inboxes}
									placeholder="Unlimited"
									disabled={submitting}
									min="0"
								/>
								<p class="text-sm text-muted-foreground mt-1">
									Maximum number of inboxes allowed. Leave empty for unlimited.
								</p>
							</div>
						</div>
					</div>
				</section>

				<!-- Form Actions -->
				<div class="flex items-center justify-end space-x-4 bg-card rounded-lg shadow-sm p-6">
					<Button type="button" variant="outline" onclick={() => goto(`/app/super_admin/accounts/${accountId}`)} disabled={submitting}>
						Cancel
					</Button>
					<Button type="submit" disabled={submitting}>
						<Save class="h-4 w-4 mr-2" />
						{submitting ? 'Saving...' : 'Save Changes'}
					</Button>
				</div>
			</form>
		{/if}
	</main>
</div>