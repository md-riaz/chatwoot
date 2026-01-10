<script lang="ts">
	import { Star } from 'lucide-svelte';
	
	interface Props {
		selectedFeatures: string[];
		allFeatures: Record<string, {
			available: boolean;
			display_name?: string;
			displayName?: string;  // API transformation might convert to camelCase
			enabled: boolean;
			premium: boolean;
			help_url?: string;
			helpUrl?: string;  // API transformation might convert to camelCase
		}>;
		onFeaturesChange: (features: string[] | Record<string, boolean>) => void;
		disabled?: boolean;
	}
	
	let { selectedFeatures = [], allFeatures = {}, onFeaturesChange, disabled = false }: Props = $props();
	
	// Get premium features dynamically from backend data
	let premiumFeatures = $derived(
		Object.keys(allFeatures).filter(feature => 
			allFeatures[feature]?.premium === true
		)
	);
	
	// Dynamically organize features from allFeatures prop
	let organizedFeatures = $derived(() => {
		const availableFeatures = Object.keys(allFeatures);
		const currentPremiumFeatures = Object.keys(allFeatures).filter(feature => 
			allFeatures[feature]?.premium === true
		);
		
		// Group features by logical categories based on their names (camelCase)
		const categories = {
			'Communication Channels': availableFeatures.filter(f => 
				f.includes('channel') || f === 'inboundEmails'
			),
			'Product Features': availableFeatures.filter(f => 
				!f.includes('channel') && 
				!f.includes('integration') && 
				!currentPremiumFeatures.includes(f) &&
				!f.includes('V2') &&
				f !== 'inboundEmails'
			),
			'Integrations': availableFeatures.filter(f => 
				f.includes('integration') && !currentPremiumFeatures.includes(f)
			),
			'Enterprise Features': availableFeatures.filter(f => 
				currentPremiumFeatures.includes(f)
			),
			'System Features': availableFeatures.filter(f => 
				f.includes('V2') || 
				f.includes('chatwoot') || 
				f.includes('search') ||
				f.includes('reply') ||
				f.includes('whatsapp') ||
				f.includes('twilio')
			)
		};
		
		// Remove empty categories
		return Object.fromEntries(
			Object.entries(categories).filter(([_, features]) => features.length > 0)
		);
	});
	
	function handleFeatureToggle(feature: string, enabled: boolean) {
		if (disabled) return;
		
		// Work directly with the feature keys as they are
		const selectedFeaturesObject: Record<string, boolean> = {};
		
		// Add existing selected features
		Object.keys(allFeatures).forEach(featureKey => {
			if (isFeatureSelected(featureKey)) {
				selectedFeaturesObject[featureKey] = true;
			}
		});
		
		// Update the toggled feature
		if (enabled) {
			selectedFeaturesObject[feature] = true;
		} else {
			delete selectedFeaturesObject[feature];
		}
		
		onFeaturesChange(selectedFeaturesObject);
	}
	
	function isFeatureSelected(feature: string): boolean {
		// selectedFeatures contains snake_case, feature is camelCase from allFeatures keys
		// Convert camelCase to snake_case for comparison
		const snakeCase = feature.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
		return selectedFeatures.includes(snakeCase);
	}
	
	function isFeatureAvailable(feature: string): boolean {
		return allFeatures[feature]?.available === true;
	}
	
	function isPremiumFeature(feature: string): boolean {
		return allFeatures[feature]?.premium === true;
	}
	
	function formatFeatureName(feature: string): string {
		// Check for display name in both snake_case and camelCase (due to API transformation)
		const featureData = allFeatures[feature];
		const displayName = featureData?.display_name || featureData?.displayName;
		
		if (displayName && displayName !== feature) {
			return displayName;
		}
		
		// Fallback: format snake_case to proper title case
		return feature
			.split('_')
			.map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
			.join(' ');
	}
	
	// Separate regular and premium features dynamically
	function getRegularFeatures() {
		const regular: Array<[string, string[]]> = [];
		const premium: Array<[string, string[]]> = [];
		
		// Get all available features
		const allAvailableFeatures = Object.keys(allFeatures).filter(f => isFeatureAvailable(f));
		
		// Separate into premium and regular
		const regularFeatures = allAvailableFeatures.filter(f => !isPremiumFeature(f));
		const premiumFeaturesList = allAvailableFeatures.filter(f => isPremiumFeature(f));
		
		// Group regular features by category
		if (regularFeatures.length > 0) {
			const regularByCategory = {
				'Communication Channels': regularFeatures.filter(f => 
					f.includes('channel_') || f === 'inbound_emails'
				),
				'Product Features': regularFeatures.filter(f => 
					!f.includes('channel_') && 
					!f.includes('integration') && 
					!f.includes('_v2') &&
					f !== 'inbound_emails'
				),
				'Integrations': regularFeatures.filter(f => 
					f.includes('integration')
				),
				'System Features': regularFeatures.filter(f => 
					f.includes('_v2') || 
					f.includes('chatwoot_') || 
					f.includes('search_') ||
					f.includes('reply_') ||
					f.includes('whatsapp_') ||
					f.includes('twilio_')
				)
			};
			
			Object.entries(regularByCategory).forEach(([category, features]) => {
				if (features.length > 0) {
					regular.push([category, features]);
				}
			});
		}
		
		// Group premium features
		if (premiumFeaturesList.length > 0) {
			premium.push(['Enterprise Features', premiumFeaturesList]);
		}
		
		return { regular, premium };
	}
	
	let { regular: regularFeatureCategories, premium: premiumFeatureCategories } = $derived(getRegularFeatures());
</script>

<div class="space-y-6">
	<!-- Debug Info (remove in production) -->
	{#if import.meta.env.DEV}
		<div class="p-4 bg-muted rounded-lg text-xs">
			<details>
				<summary class="cursor-pointer font-medium">Debug: Feature Data</summary>
				<div class="mt-2 space-y-2">
					<div><strong>Selected Features ({selectedFeatures.length}):</strong> {selectedFeatures.join(', ')}</div>
					<div><strong>All Features ({Object.keys(allFeatures).length}):</strong> {Object.keys(allFeatures).join(', ')}</div>
					<div><strong>Premium Features ({premiumFeatures.length}):</strong> {premiumFeatures.join(', ')}</div>
					<div><strong>Organized Categories:</strong> {Object.keys(organizedFeatures).join(', ')}</div>
					<div><strong>Regular Categories ({regularFeatureCategories.length}):</strong> {regularFeatureCategories.map(([cat, feats]) => `${cat}(${feats.length})`).join(', ')}</div>
					<div><strong>Premium Categories ({premiumFeatureCategories.length}):</strong> {premiumFeatureCategories.map(([cat, feats]) => `${cat}(${feats.length})`).join(', ')}</div>
					<div><strong>Sample Feature Data:</strong> {JSON.stringify(Object.entries(allFeatures)[0] || {}, null, 2)}</div>
				</div>
			</details>
		</div>
	{/if}

	<!-- Regular Features -->
	{#if regularFeatureCategories.length > 0}
		<div class="space-y-4">
			{#each regularFeatureCategories as [category, features]}
				<div>
					<h4 class="text-sm font-medium text-foreground mb-3">{category}</h4>
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
						{#each features as feature}
							<div class="flex items-center justify-between p-3 bg-card rounded-lg shadow-sm border border-border hover:bg-accent/50 transition-colors">
								<span class="text-sm text-foreground">{formatFeatureName(feature)}</span>
								<input
									type="checkbox"
									id={feature}
									checked={isFeatureSelected(feature)}
									disabled={disabled}
									onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
									class="h-4 w-4 rounded border-input text-primary focus:ring-primary focus:ring-offset-2 focus:ring-offset-background {disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}"
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
		<div class="border-t border-border pt-6">
			<div class="space-y-4">
				{#each premiumFeatureCategories as [category, features]}
					<div>
						<h4 class="text-sm font-medium text-foreground mb-3">{category}</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
							{#each features as feature}
								<div class="flex items-center justify-between p-3 bg-card rounded-lg shadow-sm border border-border hover:bg-accent/50 transition-colors">
									<div class="flex items-center gap-2">
										<span class="text-amber-500 dark:text-amber-400">
											<Star class="h-4 w-4" fill="currentColor" />
										</span>
										<span class="text-sm text-foreground">{formatFeatureName(feature)}</span>
									</div>
									<input
										type="checkbox"
										id={feature}
										checked={isFeatureSelected(feature)}
										disabled={disabled}
										onchange={(e) => handleFeatureToggle(feature, e.currentTarget.checked)}
										class="h-4 w-4 rounded border-input text-primary focus:ring-primary focus:ring-offset-2 focus:ring-offset-background {disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}"
									/>
								</div>
							{/each}
						</div>
					</div>
				{/each}
			</div>
		</div>
	{/if}
</div>