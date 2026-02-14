import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as cannedResponsesApi from '$lib/api/cannedResponses';
import type {
  CannedResponse,
  CannedResponseListParams,
} from '$lib/api/cannedResponses';

class CannedResponsesStore {
  items = $state<CannedResponse[]>([]);
  isLoading = $state(false);
  isDeleting = $state(false);
  error = $state<string | null>(null);
  currentPage = $state(1);
  lastPage = $state(1);
  perPage = $state(15);
  total = $state(0);

  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  async fetch(params: CannedResponseListParams = {}): Promise<void> {
    if (!this.currentAccountId) return;

    this.isLoading = true;
    this.error = null;

    try {
      const response = await cannedResponsesApi.getCannedResponses(
        this.currentAccountId,
        params
      );
      this.items = response.data || [];
      this.currentPage = response.meta?.currentPage || params.page || 1;
      this.lastPage = response.meta?.lastPage || 1;
      this.perPage = response.meta?.perPage || 15;
      this.total = response.meta?.total || 0;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch canned responses';
      this.items = [];
    } finally {
      this.isLoading = false;
    }
  }

  async delete(id: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    this.isDeleting = true;
    this.error = null;

    try {
      await cannedResponsesApi.deleteCannedResponse(this.currentAccountId, id);
      this.items = this.items.filter(item => item.id !== id);
      this.total = Math.max(0, this.total - 1);
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete canned response';
      return false;
    } finally {
      this.isDeleting = false;
    }
  }
}

export const cannedResponsesStore = new CannedResponsesStore();
