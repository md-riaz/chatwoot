import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as integrationsApi from '$lib/api/integrations';
import type { Integration } from '$lib/api/integrations';

class IntegrationsStore {
  integrations = $state<Integration[]>([]);
  isLoading = $state(false);
  error = $state<string | null>(null);

  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  get connectedCount(): number {
    return this.integrations.filter(item => item.status === 'connected').length;
  }

  async fetch(): Promise<void> {
    if (!this.currentAccountId) return;

    this.isLoading = true;
    this.error = null;

    try {
      this.integrations = await integrationsApi.getIntegrations(
        this.currentAccountId
      );
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch integrations';
      this.integrations = [];
    } finally {
      this.isLoading = false;
    }
  }
}

export const integrationsStore = new IntegrationsStore();
