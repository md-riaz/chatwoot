import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as billingApi from '$lib/api/billing';
import type { BillingInvoice, BillingPlan } from '$lib/api/billing';

class BillingStore {
  plan = $state<BillingPlan | null>(null);
  invoices = $state<BillingInvoice[]>([]);
  isLoading = $state(false);
  error = $state<string | null>(null);

  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  async fetch(): Promise<void> {
    if (!this.currentAccountId) return;

    this.isLoading = true;
    this.error = null;

    try {
      const [plan, invoices] = await Promise.all([
        billingApi.getCurrentPlan(this.currentAccountId),
        billingApi.getInvoices(this.currentAccountId),
      ]);

      this.plan = plan;
      this.invoices = invoices;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch billing information';
      this.plan = null;
      this.invoices = [];
    } finally {
      this.isLoading = false;
    }
  }
}

export const billingStore = new BillingStore();
