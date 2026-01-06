<script lang="ts">
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Label } from '$lib/components/ui/label';
	
	interface Props {
		selectedFeatures: string[];
		allFeatures: Record<string, boolean>;
		onFeaturesChange: (features: string[]) => void;
		disabled?: boolean;
	}
	
	let { selectedFeatures = [], allFeatures = {}, onFeaturesChange, disabled = false }: Props = $props();
	
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
			'disable_branding', 'advanced_reporting'
		]
	};
	
	function handleFeatureToggle(feature: string, enabled: boolean) {
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
</script>

<Card>
	<CardHeader>
		<CardTitle>Feature Flags</CardTitle>
		<CardDescription>
			Enable or disable features for this account. Only available features can be enabled.
		</CardDescription>
	</CardHeader>
	<CardContent class="space-y-6">
		{#each Object.entries(featureCategories) as [category, features]}
			<div class="space-y-3">
				<h4 class="text-sm font-medium text-foreground">{category}</h4>
				<div class="grid grid-cols-2 gap-3">
					{#each features as feature}
						{#if isFeatureAvailable(feature)}
							<div class="flex items-center space-x-2">
								<input
									type="checkbox"
									id={feature}
									checked={isFeatureSelected(feature)}
									{disabled}
									onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
									class="rounded"
								/>
								<Label for={feature} class="text-sm">
									{feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
								</Label>
							</div>
						{/if}
					{/each}
				</div>
			</div>
		{/each}
		
		<!-- Show any additional features not in categories -->
		{#if Object.keys(allFeatures).some(f => !Object.values(featureCategories).flat().includes(f))}
			<div class="space-y-3">
				<h4 class="text-sm font-medium text-foreground">Other Features</h4>
				<div class="grid grid-cols-2 gap-3">
					{#each Object.keys(allFeatures) as feature}
						{#if !Object.values(featureCategories).flat().includes(feature) && isFeatureAvailable(feature)}
							<div class="flex items-center space-x-2">
								<input
									type="checkbox"
									id={feature}
									checked={isFeatureSelected(feature)}
									{disabled}
									onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
									class="rounded"
								/>
								<Label for={feature} class="text-sm">
									{feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
								</Label>
							</div>
						{/if}
					{/each}
				</div>
			</div>
		{/if}
	</CardContent>
</Card>