import { browser } from '$app/environment';

/**
 * Global Config Store using Svelte 5 runes
 * Manages application-wide configuration
 */
class GlobalConfigStore {
  config = $state<Record<string, any>>({});
  
  constructor() {
    if (browser && (window as any).globalConfig) {
      this.config = (window as any).globalConfig;
    } else if (browser && (window as any).chatwootConfig) {
        this.config = (window as any).chatwootConfig;
    }
  }

  get(key: string) {
    return this.config[key];
  }

  isFeatureEnabled(feature: string) {
    // Check if feature is enabled in global config
    // This is a simplified check, actual implementation might need to check account specific features too
    return !!this.config[feature];
  }
}

export const globalConfig = new GlobalConfigStore();
