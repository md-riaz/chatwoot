import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as portalsAPI from '$lib/api/portals';
import type {
    Portal,
    CreatePortalParams,
    UpdatePortalParams,
} from '$lib/api/portals';

class PortalsStore {
    // Reactive state
    allPortals = $state<Portal[]>([]);
    bgPortals = $state<Portal[]>([]); // Background copy for optimistic updates or other needs
    selectedPortalSlug = $state<string | null>(null);

    error = $state<string | null>(null);

    uiFlags = $state({
        isFetching: false,
        isCreating: false,
        isUpdating: false,
        isDeleting: false,
    });

    // Computed
    selectedPortal = $derived(
        this.allPortals.find(p => p.slug === this.selectedPortalSlug) || null
    );

    get currentAccountId(): number {
        const pageStore = get(page);
        return Number(pageStore.params.accountId);
    }

    async fetchPortals(): Promise<void> {
        const accountId = this.currentAccountId;
        if (!accountId) return;

        this.uiFlags.isFetching = true;
        this.error = null;

        try {
            const portals = await portalsAPI.getPortals(accountId);
            this.allPortals = portals;
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch portals';
            console.error('Error fetching portals:', err);
        } finally {
            this.uiFlags.isFetching = false;
        }
    }

    async createPortal(data: CreatePortalParams): Promise<Portal | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isCreating = true;
        this.error = null;

        try {
            const portal = await portalsAPI.createPortal(accountId, data);
            this.allPortals = [...this.allPortals, portal];
            return portal;
        } catch (err: any) {
            this.error = err.message || 'Failed to create portal';
            console.error('Error creating portal:', err);
            return null;
        } finally {
            this.uiFlags.isCreating = false;
        }
    }

    async updatePortal(slug: string, data: UpdatePortalParams): Promise<Portal | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isUpdating = true;
        this.error = null;

        try {
            const updatedPortal = await portalsAPI.updatePortal(accountId, slug, data);

            const index = this.allPortals.findIndex(p => p.slug === slug);
            if (index !== -1) {
                this.allPortals[index] = updatedPortal;
                // Trigger reactivity might need reassignment if using arrays directly with Runes?
                // $state array mutation usually triggers, but reassignment is safer
                // this.allPortals = [...this.allPortals]; 
                // With Runes, deep reactivity on arrays implies mutation works if proxy is used, 
                // but let's stick to safe pattern.
            }
            return updatedPortal;
        } catch (err: any) {
            this.error = err.message || 'Failed to update portal';
            console.error('Error updating portal:', err);
            return null;
        } finally {
            this.uiFlags.isUpdating = false;
        }
    }

    async deletePortal(slug: string): Promise<boolean> {
        const accountId = this.currentAccountId;
        if (!accountId) return false;

        this.uiFlags.isDeleting = true;
        this.error = null;

        const previousPortals = this.allPortals;
        this.allPortals = this.allPortals.filter(p => p.slug !== slug);

        try {
            await portalsAPI.deletePortal(accountId, slug);
            return true;
        } catch (err: any) {
            this.allPortals = previousPortals;
            this.error = err.message || 'Failed to delete portal';
            console.error('Error deleting portal:', err);
            return false;
        } finally {
            this.uiFlags.isDeleting = false;
        }
    }

    selectPortal(slug: string | null): void {
        this.selectedPortalSlug = slug;
    }
}

export const portalsStore = new PortalsStore();
