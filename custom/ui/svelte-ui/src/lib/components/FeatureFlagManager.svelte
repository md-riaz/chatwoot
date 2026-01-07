<script lang="ts">
	import { Star } from 'lucide-svelte';
	
	interface Props {
		selectedFeatures: string[];
		allFeatures: Record<string, boolean>;
		onFeaturesChange: (features: string[]) => void;
		disabled?: boolean;
	}
	
	let { selectedFeatures = [], allFeatures = {}, onFeaturesChange, disabled = false }: Props = $props();
	
	// Premium features that require special handling
	const premiumFeatures = [
		'captain', 'custom_branding', 'agent_capacity', 'audit_logs',
		'disable_branding', 'advanced_reporting', 'crm_integration', 
		'notion_integration', 'inbox_assistant'
	];
	
	// Group features by category for better organization
	const featureCategories = {
		'Communication Channels': [
			'live_chat', 'email', 'sms', 'messenger', 'instagram', 
			'whatsapp', 'telegram', 'line', 'tiktok'
		],
		'Product Features': [
			'help_center', 'macros', 'canned_responses', 'labels', 'teams',
			'custom_attributes', 'automation_rules', 'webhooks', 'campaigns', 'reports'
		],
		'Authentication & OAuth': [
			'google', 'microsoft', 'saml'
		],
		'Integrations': [
			'linear', 'slack', 'shopify', 'notion_integration', 'crm_integration'
		],
		'Enterprise Features': [
			'captain', 'custom_branding', 'agent_capacity', 'audit_logs',
			'disable_branding', 'advanced_reporting', 'inbox_assistant'
		]
	};
	
	function handleFeatureToggle(feature: string, enabled: boolean) {
		if (disabled) return;
		
		let newFeatures = [...selectedFeatures];
		
		if (enabled && !newFeatures.includes(feature)) {
			newFeatures.push(feature);
		} else if (!enabled && newFeatures.includes(feature)) {
			newFeatures = newFeatures.filter(f => f !== feature);
		}
		
		onFeaturesChange(newFeatures);
	}
	
	function isFeatureSelected(feature: string): boolean {
		return selectedFeatures.includes(feature);
	}
	
	function isFeatureAvailable(feature: string): boolean {
		return allFeatures[feature] === true;
	}
	
	function isPremiumFeature(feature: string): boolean {
		return premiumFeatures.includes(feature);
	}
	
	function formatFeatureName(feature: string): string {
		return feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
	}
	
	// Separate regular and premium features
	function getRegularFeatures() {
		const regular: Array<[string, string[]]> = [];
		const premium: Array<[string, string[]]> = [];
		
		Object.entries(featureCategories).forEach(([category, features]) => {
			const categoryRegular = features.filter(f => !isPremiumFeature(f) && isFeatureAvailable(f));
			const categoryPremium = features.filter(f => isPremiumFeature(f) && isFeatureAvailable(f));
			
			if (categoryRegular.length > 0) {
				regular.push([category, categoryRegular]);
			}
			if (categoryPremium.length > 0) {
				premium.push([category, categoryPremium]);
			}
		});
		
		return { regular, premium };
	}
	
	let { regular: regularFeatureCategories, premium: premiumFeatureCategories } = $derived(getRegularFeatures());
</script>

<div class="space-y-6">
	<!-- Regular Features -->
	{#if regularFeatureCategories.length > 0}
		<div class="space-y-4">
			{#each regularFeatureCategories as [category, features]}
				<div>
					<h4 class="text-sm font-medium text-slate-700 mb-3">{category}</h4>
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
						{#each features as feature}
							<div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm border border-slate-200">
								<span class="text-sm text-slate-700">{formatFeatureName(feature)}</span>
								<input
									type="checkbox"
									id={feature}
									checked={isFeatureSelected(feature)}
									disabled={disabled}
									onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
									class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-600 {disabled ? 'opacity-50 cursor-not-allowed' : ''}"
								/>
							</div>
						{/each}
					</div>
				</div>
			{/each}
		</div>
	{/if}

	<!-- Premium Features -->
	{#if premiumFeatureCategories.length > 0}
		<div class="border-t border-slate-200 pt-6">
			<div class="space-y-4">
				{#each premiumFeatureCategories as [category, features]}
					<div>
						<h4 class="text-sm font-medium text-slate-700 mb-3">{category}</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
							{#each features as feature}
								<div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm border border-slate-200">
									<div class="flex items-center gap-2">
										<span class="text-amber-500">
											<Star class="h-4 w-4" fill="currentColor" />
										</span>
										<span class="text-sm text-slate-700">{formatFeatureName(feature)}</span>
									</div>
									<input
										type="checkbox"
										id={feature}
										checked={isFeatureSelected(feature)}
										disabled={disabled}
										onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
										class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-600 {disabled ? 'opacity-50 cursor-not-allowed' : ''}"
									/>
								</div>
							{/each}
						</div>
					</div>
				{/each}
			</div>
		</div>
	{/if}

	<!-- Show any additional features not in categories -->
	{#if Object.keys(allFeatures).some(f => !Object.values(featureCategories).flat().includes(f))}
		<div class="border-t border-slate-200 pt-6">
			<h4 class="text-sm font-medium text-slate-700 mb-3">Other Features</h4>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
				{#each Object.keys(allFeatures) as feature}
					{#if !Object.values(featureCategories).flat().includes(feature) && isFeatureAvailable(feature)}
						<div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm border border-slate-200">
							<div class="flex items-center gap-2">
								{#if isPremiumFeature(feature)}
									<span class="text-amber-500">
										<Star class="h-4 w-4" fill="currentColor" />
									</span>
								{/if}
								<span class="text-sm text-slate-700">{formatFeatureName(feature)}</span>
							</div>
							<input
								type="checkbox"
								id={feature}
								checked={isFeatureSelected(feature)}
								disabled={disabled}
								onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
								class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-600 {disabled ? 'opacity-50 cursor-not-allowed' : ''}"
							/>
						</div>
					{/if}
				{/each}
			</div>
		</div>
	{/if}
</div>