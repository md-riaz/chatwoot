<script lang="ts">
	import { superAdminAPI } from '$lib/api/superAdmin';
	import Button from '$lib/components/ui/button/button.svelte';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import Checkbox from '$lib/components/ui/checkbox/checkbox.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Skeleton from '$lib/components/ui/skeleton/skeleton.svelte';
	import Textarea from '$lib/components/ui/textarea/textarea.svelte';
	import * as Select from '$lib/components/ui/select';
	import { 
		Save, 
		Settings as SettingsIcon, 
		Lock,
		LockOpen,
		RefreshCw,
		AlertCircle
	} from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';
	import { page } from '$app/stores';
	import { getErrorMessage } from '$lib/api/errors';

	// Svelte 5 state management
	let loading = $state(true);
	let saving = $state(false);
	let refreshing = $state(false);
	let settingsData: Record<string, any> = $state({});
	let currentCategorySettings: any[] = $state([]);

	// Category display names (matching Rails Vue system)
	const categoryNames: Record<string, string> = {
		general: 'General',
		email: 'Email',
		messenger: 'Messenger',
		instagram: 'Instagram',
		tiktok: 'TikTok',
		google: 'Google',
		microsoft: 'Microsoft',
		linear: 'Linear',
		notion: 'Notion',
		slack: 'Slack',
		whatsapp_embedded: 'WhatsApp Embedded',
		shopify: 'Shopify'
	};

	// Derived values using Svelte 5 $derived
	let activeCategory = $derived($page.url.searchParams.get('config') || 'general');
	let categoryDisplayName = $derived(categoryNames[activeCategory] || activeCategory);
	let hasSettings = $derived(currentCategorySettings.length > 0);

	// Format field names for display (convert snake_case to Title Case)
	function formatFieldName(name: string): string {
		return name
			.replace(/_/g, ' ')
			.replace(/\b\w/g, l => l.toUpperCase())
			.replace(/\bId\b/g, 'ID')
			.replace(/\bApi\b/g, 'API')
			.replace(/\bUrl\b/g, 'URL')
			.replace(/\bUri\b/g, 'URI')
			.replace(/\bSso\b/g, 'SSO')
			.replace(/\bSaml\b/g, 'SAML')
			.replace(/\bIdp\b/g, 'IDP')
			.replace(/\bSmtp\b/g, 'SMTP')
			.replace(/\bOauth\b/g, 'OAuth')
			.replace(/\bFb\b/g, 'Facebook')
			.replace(/\bTiktok\b/g, 'TikTok')
			.replace(/\bWhatsapp\b/g, 'WhatsApp');
	}

	// Get field description or generate one from the field name
	function getFieldDescription(setting: any): string {
		if (setting.description) {
			return setting.description;
		}
		
		// Generate helpful descriptions for common field patterns
		const name = setting.name.toLowerCase();
		if (name.includes('client_id')) return 'OAuth Client ID for authentication';
		if (name.includes('client_secret')) return 'OAuth Client Secret for authentication';
		if (name.includes('app_id')) return 'Application ID for integration';
		if (name.includes('app_secret')) return 'Application Secret for integration';
		if (name.includes('verify_token')) return 'Webhook verification token';
		if (name.includes('api_version')) return 'API version to use for requests';
		if (name.includes('redirect_uri')) return 'OAuth redirect URI';
		if (name.includes('webhook_secret')) return 'Secret for webhook verification';
		if (name.includes('timeout')) return 'Request timeout in seconds';
		if (name.includes('size') || name.includes('limit')) return 'Size limit in MB';
		if (name.includes('domain')) return 'Domain configuration';
		if (name.includes('enable')) return 'Enable or disable this feature';
		
		return `Configure ${formatFieldName(setting.name).toLowerCase()} setting`;
	}

	async function loadSettings() {
		loading = true;
		try {
			const response = await superAdminAPI.settings.getByCategory(activeCategory);
			currentCategorySettings = response.data || [];
			
			// Initialize settings data
			settingsData = {};
			currentCategorySettings.forEach((setting: any) => {
				// For boolean fields, ensure proper string value for Select component
				if (setting.type === 'boolean') {
					settingsData[setting.name] = String(Boolean(setting.value));
				} else {
					settingsData[setting.name] = setting.value;
				}
			});
		} catch (error) {
			toast.error('Failed to load settings');
			console.error(error);
		} finally {
			loading = false;
		}
	}

	async function refreshSettings() {
		refreshing = true;
		try {
			// Call refresh endpoint if available, otherwise just reload
			await fetch('/api/v1/super_admin/settings/refresh', { method: 'POST' });
			await loadSettings();
			toast.success('Settings refreshed successfully');
		} catch (error) {
			// Fallback to just reloading
			await loadSettings();
			toast.success('Settings reloaded');
		} finally {
			refreshing = false;
		}
	}

	async function handleSave() {
		if (!hasSettings) {
			toast.error('No settings to save');
			return;
		}

		saving = true;
		try {
			// Prepare settings for the category
			const categorySettings: Record<string, any> = {};
			currentCategorySettings.forEach((setting: any) => {
				const value = settingsData[setting.name];
				// Convert string booleans back to actual booleans
				if (setting.type === 'boolean') {
					categorySettings[setting.name] = value === 'true' || value === true;
				} else {
					categorySettings[setting.name] = value;
				}
			});

			await superAdminAPI.settings.update({ settings: categorySettings });
			toast.success('Settings updated successfully');
		} catch (error: any) {
			// Use getErrorMessage to properly format validation errors
			const errorMessage = getErrorMessage(error);
			toast.error(errorMessage || 'Failed to update settings');
			console.error(error);
		} finally {
			saving = false;
		}
	}

	function updateSettingValue(settingName: string, value: any) {
		settingsData[settingName] = value;
	}

	function getSelectOptions(settingName: string): string[] {
		if (settingName.includes('API_VERSION')) {
			return ['v18.0', 'v17.0', 'v16.0', 'v15.0'];
		}
		if (settingName === 'TIKTOK_API_VERSION') {
			return ['v1.0', 'v1.1', 'v1.2'];
		}
		return [];
	}

	// Helper function to get display value for select trigger
	function getBooleanDisplay(value: any): string {
		return value === true || value === 'true' ? 'True' : 'False';
	}

	// Watch for category changes and reload settings
	$effect(() => {
		loadSettings();
	});

	onMount(() => {
		loadSettings();
	});
</script>

<div class="h-full flex flex-col bg-background">
	<!-- Header matching other SuperAdmin pages -->
	<div class="flex items-center justify-between px-8 py-6 border-b bg-card">
		<div class="flex items-center gap-4">
			<h1 class="text-2xl font-semibold text-foreground">Configure Settings - {categoryDisplayName}</h1>
		</div>
		<div class="flex items-center gap-3">
			<Button 
				variant="outline" 
				size="sm" 
				onclick={refreshSettings} 
				disabled={refreshing}
			>
				<RefreshCw class="h-4 w-4 mr-2 {refreshing ? 'animate-spin' : ''}" />
				{refreshing ? 'Refreshing...' : 'Refresh'}
			</Button>
			{#if hasSettings}
				<Button onclick={handleSave} disabled={saving || loading}>
					<Save class="h-4 w-4 mr-2" />
					{saving ? 'Saving...' : 'Submit'}
				</Button>
			{/if}
		</div>
	</div>

	<!-- Content Area -->
	<div class="flex-1 overflow-auto p-8">
		{#if loading}
			<div class="max-w-4xl mx-auto space-y-6">
				<Card>
					<CardHeader>
						<Skeleton class="h-6 w-48" />
						<Skeleton class="h-4 w-96" />
					</CardHeader>
					<CardContent class="space-y-6">
						<Skeleton class="h-10 w-full" />
						<Skeleton class="h-10 w-full" />
						<Skeleton class="h-10 w-full" />
					</CardContent>
				</Card>
			</div>
		{:else if hasSettings}
			<div class="max-w-4xl mx-auto">
				<Card>
					<CardHeader>
						<CardTitle class="flex items-center gap-2">
							<SettingsIcon class="h-5 w-5" />
							{categoryDisplayName} Configuration
						</CardTitle>
						<CardDescription>
							Configure {categoryDisplayName.toLowerCase()} settings for your ClearLine instance.
						</CardDescription>
					</CardHeader>
					<CardContent class="space-y-8">
						{#each currentCategorySettings as setting, index}
							{@const value = settingsData[setting.name]}
							<div class="space-y-3 {index > 0 ? 'pt-6 border-t border-border' : ''}">
								<div class="flex items-start justify-between">
									<div class="flex-1">
										<div class="flex items-center gap-2 mb-1">
											<Label for={setting.name} class="text-sm font-semibold text-foreground">
												{setting.display_title || formatFieldName(setting.name)}
											</Label>
											{#if setting.type === 'secret'}
												<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
													Secret
												</span>
											{:else if setting.type === 'boolean'}
												<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
													Boolean
												</span>
											{:else if setting.type === 'integer'}
												<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
													Number
												</span>
											{:else if setting.type === 'code'}
												<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
													Code
												</span>
											{:else if setting.type === 'select'}
												<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400">
													Select
												</span>
											{/if}
											{#if setting.locked}
												<Lock class="h-4 w-4 text-amber-600" />
											{:else}
												<LockOpen class="h-4 w-4 text-muted-foreground" />
											{/if}
										</div>
										
										{#if setting.description}
											<p class="text-sm text-muted-foreground mb-3">{setting.description}</p>
										{:else}
											<p class="text-sm text-muted-foreground mb-3">{getFieldDescription(setting)}</p>
										{/if}
									</div>
								</div>

								<div class="space-y-2 max-w-lg">
									{#if setting.type === 'boolean'}
										<Select.Root 
											type="single"
											bind:value={settingsData[setting.name]}
											name={setting.name}
											disabled={setting.locked}
										>
											<Select.Trigger class="w-full">
												{getBooleanDisplay(settingsData[setting.name])}
											</Select.Trigger>
											<Select.Content>
												<Select.Item value="true" label="True">True</Select.Item>
												<Select.Item value="false" label="False">False</Select.Item>
											</Select.Content>
										</Select.Root>
									{:else if setting.type === 'code'}
										<Textarea
											id={setting.name}
											value={value || ''}
											disabled={setting.locked}
											placeholder="Enter configuration..."
											class="font-mono text-sm min-h-[120px] resize-y max-w-none"
											oninput={(e: Event & { currentTarget: HTMLTextAreaElement }) => updateSettingValue(setting.name, e.currentTarget.value)}
										/>
									{:else if setting.type === 'select'}
										{@const options = getSelectOptions(setting.name)}
										<Select.Root 
											type="single"
											bind:value={settingsData[setting.name]}
											name={setting.name}
											disabled={setting.locked}
										>
											<Select.Trigger class="w-full">
												{settingsData[setting.name] || `Select ${setting.display_title?.toLowerCase() || formatFieldName(setting.name).toLowerCase()}...`}
											</Select.Trigger>
											<Select.Content>
												{#each options as option}
													<Select.Item value={option} label={option}>{option}</Select.Item>
												{/each}
											</Select.Content>
										</Select.Root>
									{:else}
										<Input
											id={setting.name}
											type={setting.type === 'secret' ? 'password' : setting.type === 'integer' ? 'number' : 'text'}
											value={value || ''}
											disabled={setting.locked}
											placeholder={setting.type === 'secret' ? '••••••••' : `Enter ${setting.display_title?.toLowerCase() || formatFieldName(setting.name).toLowerCase()}`}
											class="w-full"
											oninput={(e: Event & { currentTarget: HTMLInputElement }) => updateSettingValue(setting.name, e.currentTarget.value)}
										/>
									{/if}

									{#if setting.locked}
										<div class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 dark:bg-amber-950/20 p-3 rounded-md border border-amber-200 dark:border-amber-800">
											<AlertCircle class="h-4 w-4 shrink-0" />
											<span>This setting is locked and cannot be modified.</span>
										</div>
									{/if}
								</div>
							</div>
						{/each}
					</CardContent>
				</Card>
			</div>
		{:else}
			<div class="max-w-4xl mx-auto">
				<Card>
					<CardContent class="flex flex-col items-center justify-center py-16 text-center">
						<div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
							<SettingsIcon class="h-8 w-8 text-muted-foreground" />
						</div>
						<h3 class="text-xl font-semibold text-foreground mb-2">
							{activeCategory ? 'No Settings Available' : 'Select a Configuration Category'}
						</h3>
						<p class="text-muted-foreground max-w-md">
							{activeCategory 
								? `No configuration options are available for ${categoryDisplayName}.`
								: 'Choose a category from the sidebar to view and edit configuration options.'
							}
						</p>
						{#if !activeCategory}
							<div class="mt-6 text-sm text-muted-foreground">
								<p>Available categories: General, SAML SSO, Email, Messenger, and more.</p>
							</div>
						{/if}
					</CardContent>
				</Card>
			</div>
		{/if}
	</div>
</div>