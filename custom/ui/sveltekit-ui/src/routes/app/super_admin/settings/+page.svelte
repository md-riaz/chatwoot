<script lang="ts">
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';
	import { Save, Settings as SettingsIcon, Shield, Cog, Plug, Globe } from 'lucide-svelte';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Checkbox from '$lib/components/ui/checkbox/checkbox.svelte';
	import Skeleton from '$lib/components/ui/skeleton/skeleton.svelte';
	import { superAdminAPI } from '$lib/api/client';

	type Tab = 'general' | 'platform' | 'system' | 'security' | 'integration';

	let activeTab: Tab = 'general';
	let loading = true;
	let saving = false;
	let settings: Record<string, any> = {};

	const tabs = [
		{ id: 'general' as Tab, label: 'General', icon: SettingsIcon },
		{ id: 'platform' as Tab, label: 'Platform', icon: Globe },
		{ id: 'system' as Tab, label: 'System', icon: Cog },
		{ id: 'security' as Tab, label: 'Security', icon: Shield },
		{ id: 'integration' as Tab, label: 'Integration', icon: Plug }
	];

	async function loadSettings() {
		loading = true;
		try {
			settings = await superAdminAPI.settings.get();
		} catch (error) {
			toast.error('Failed to load settings');
			console.error(error);
		} finally {
			loading = false;
		}
	}

	async function handleSave() {
		saving = true;
		try {
			await superAdminAPI.settings.update(settings);
			toast.success('Settings updated successfully');
		} catch (error: any) {
			toast.error(error.message || 'Failed to update settings');
			console.error(error);
		} finally {
			saving = false;
		}
	}

	onMount(() => {
		loadSettings();
	});
</script>

<div class="h-full flex flex-col bg-white dark:bg-slate-1">
	<div class="flex items-center justify-between px-8 py-6 border-b border-slate-6">
		<h1 class="text-2xl font-semibold text-slate-12">Settings</h1>
		<Button onclick={handleSave} disabled={saving || loading}>
			<Save class="h-4 w-4 mr-2" />
			{saving ? 'Saving...' : 'Save Changes'}
		</Button>
	</div>

	<div class="flex-1 flex overflow-hidden">
		<!-- Tabs Sidebar -->
		<div class="w-64 border-r border-slate-6 p-2">
			{#each tabs as tab}
				<button
					type="button"
					onclick={() => (activeTab = tab.id)}
					class={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ${
						activeTab === tab.id
							? 'bg-iris-2 text-iris-11 dark:bg-iris-3'
							: 'text-slate-11 hover:bg-slate-2 dark:hover:bg-slate-3'
					}`}
				>
					<svelte:component this={tab.icon} class="h-4 w-4" />
					{tab.label}
				</button>
			{/each}
		</div>

		<!-- Content Area -->
		<div class="flex-1 overflow-auto p-8">
			{#if loading}
				<div class="max-w-2xl space-y-6">
					<Skeleton class="h-10 w-full" />
					<Skeleton class="h-10 w-full" />
					<Skeleton class="h-10 w-full" />
				</div>
			{:else}
				<!-- General Settings -->
				{#if activeTab === 'general'}
					<div class="max-w-2xl space-y-6">
						<div>
							<h2 class="text-lg font-semibold text-slate-12 mb-4">General Settings</h2>
							<p class="text-sm text-slate-10 mb-6">
								Configure general instance settings and branding options.
							</p>
						</div>

						<div class="space-y-2">
							<Label for="instance_name">Instance Name</Label>
							<Input
								id="instance_name"
								bind:value={settings.instance_name}
								placeholder="My Chatwoot Instance"
							/>
						</div>

						<div class="space-y-2">
							<Label for="support_email">Support Email</Label>
							<Input
								id="support_email"
								type="email"
								bind:value={settings.support_email}
								placeholder="support@example.com"
							/>
						</div>

						<div class="space-y-2">
							<Label for="reply_time">Reply Time (in minutes)</Label>
							<Input
								id="reply_time"
								type="number"
								bind:value={settings.reply_time}
								placeholder="5"
							/>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="enable_widget" bind:checked={settings.display_support_chat_widget} />
							<Label for="enable_widget" class="cursor-pointer">
								Display Support Chat Widget
							</Label>
						</div>
					</div>
				{/if}

				<!-- Platform Settings -->
				{#if activeTab === 'platform'}
					<div class="max-w-2xl space-y-6">
						<div>
							<h2 class="text-lg font-semibold text-slate-12 mb-4">Platform Settings</h2>
							<p class="text-sm text-slate-10 mb-6">
								Control platform features and user capabilities.
							</p>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="enable_account_signup" bind:checked={settings.enable_account_signup} />
							<Label for="enable_account_signup" class="cursor-pointer">
								Enable Account Signup
							</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="enable_user_signup" bind:checked={settings.enable_user_signup} />
							<Label for="enable_user_signup" class="cursor-pointer">Enable User Signup</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="enable_inbox_creation" bind:checked={settings.enable_inbox_creation} />
							<Label for="enable_inbox_creation" class="cursor-pointer">
								Enable Inbox Creation
							</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="disable_branding" bind:checked={settings.disable_branding} />
							<Label for="disable_branding" class="cursor-pointer">Disable Branding</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="auto_assign" bind:checked={settings.auto_assign_conversations} />
							<Label for="auto_assign" class="cursor-pointer">
								Auto-assign Conversations to Agents
							</Label>
						</div>
					</div>
				{/if}

				<!-- System Settings -->
				{#if activeTab === 'system'}
					<div class="max-w-2xl space-y-6">
						<div>
							<h2 class="text-lg font-semibold text-slate-12 mb-4">System Settings</h2>
							<p class="text-sm text-slate-10 mb-6">
								Configure system limits and performance settings.
							</p>
						</div>

						<div class="space-y-2">
							<Label for="max_accounts">Max Accounts per User</Label>
							<Input
								id="max_accounts"
								type="number"
								bind:value={settings.max_accounts_per_user}
								placeholder="10"
							/>
						</div>

						<div class="space-y-2">
							<Label for="max_inboxes">Max Inboxes per Account</Label>
							<Input
								id="max_inboxes"
								type="number"
								bind:value={settings.max_inboxes_per_account}
								placeholder="25"
							/>
						</div>

						<div class="space-y-2">
							<Label for="max_users">Max Users per Account</Label>
							<Input
								id="max_users"
								type="number"
								bind:value={settings.max_users_per_account}
								placeholder="100"
							/>
						</div>

						<div class="space-y-2">
							<Label for="rate_limit">Rate Limit per User (requests/hour)</Label>
							<Input
								id="rate_limit"
								type="number"
								bind:value={settings.rate_limit_per_user}
								placeholder="1000"
							/>
						</div>
					</div>
				{/if}

				<!-- Security Settings -->
				{#if activeTab === 'security'}
					<div class="max-w-2xl space-y-6">
						<div>
							<h2 class="text-lg font-semibold text-slate-12 mb-4">Security Settings</h2>
							<p class="text-sm text-slate-10 mb-6">
								Configure security and authentication settings.
							</p>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="enforce_validation" bind:checked={settings.enforce_user_identity_validation} />
							<Label for="enforce_validation" class="cursor-pointer">
								Enforce User Identity Validation
							</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="force_ssl" bind:checked={settings.force_ssl} />
							<Label for="force_ssl" class="cursor-pointer">Force SSL</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="api_rate_limit" bind:checked={settings.enable_api_rate_limiting} />
							<Label for="api_rate_limit" class="cursor-pointer">Enable API Rate Limiting</Label>
						</div>

						<div class="flex items-center gap-2">
							<Checkbox id="webhook_signature" bind:checked={settings.enable_webhook_signature} />
							<Label for="webhook_signature" class="cursor-pointer">
								Enable Webhook Signature Verification
							</Label>
						</div>
					</div>
				{/if}

				<!-- Integration Settings -->
				{#if activeTab === 'integration'}
					<div class="max-w-2xl space-y-6">
						<div>
							<h2 class="text-lg font-semibold text-slate-12 mb-4">Integration Settings</h2>
							<p class="text-sm text-slate-10 mb-6">
								Configure API endpoints and integration URLs.
							</p>
						</div>

						<div class="space-y-2">
							<Label for="api_base_url">API Base URL</Label>
							<Input
								id="api_base_url"
								bind:value={settings.api_base_url}
								placeholder="https://api.example.com"
							/>
						</div>

						<div class="space-y-2">
							<Label for="widget_base_url">Widget Base URL</Label>
							<Input
								id="widget_base_url"
								bind:value={settings.widget_base_url}
								placeholder="https://widget.example.com"
							/>
						</div>

						<div class="pt-6 border-t border-slate-6">
							<h3 class="text-sm font-semibold text-slate-12 mb-3">Documentation</h3>
							<div class="space-y-2 text-sm">
								<a
									href="/api/docs"
									target="_blank"
									class="block text-iris-11 hover:text-iris-12 hover:underline"
								>
									API Documentation
								</a>
								<a
									href="/docs/webhooks"
									target="_blank"
									class="block text-iris-11 hover:text-iris-12 hover:underline"
								>
									Webhook Documentation
								</a>
								<a
									href="/docs/widgets"
									target="_blank"
									class="block text-iris-11 hover:text-iris-12 hover:underline"
								>
									Widget Integration Guide
								</a>
							</div>
						</div>
					</div>
				{/if}
			{/if}
		</div>
	</div>
</div>
