import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as segmentsAPI from '$lib/api/segments';
import type { Segment } from '$lib/api/segments';
import { authStore } from './auth.svelte';

/**
 * Segments Store using Svelte 5 Runes
 * Manages segment data and operations
 */
class SegmentsStore {
    // Reactive state using $state rune
    allSegments = $state<Segment[]>([]);
    isLoading = $state<boolean>(false);
    error = $state<string | null>(null);

    // Getter for current account ID from route
    get currentAccountId(): number {
        const pageStore = get(page);
        const routeAccountId = pageStore.params.accountId;

        // Try to get accountId from route params first
        if (routeAccountId) {
            const parsed = parseInt(routeAccountId, 10);
            if (!isNaN(parsed) && parsed > 0) {
                return parsed;
            }
        }

        // Fall back to user's current account ID (with null safety)
        return authStore.currentUser?.accountId ?? 0;
    }

    // Getter for segments count
    get segmentsCount(): number {
        return Array.isArray(this.allSegments) ? this.allSegments.length : 0;
    }

    /**
     * Fetch all segments
     */
    async fetchSegments(): Promise<void> {
        const accountId = this.currentAccountId;
        if (!accountId) return;

        this.isLoading = true;
        this.error = null;

        try {
            const segments = await segmentsAPI.getSegments(accountId);
            if (Array.isArray(segments)) {
                this.allSegments = segments;
            } else {
                this.allSegments = [];
                this.error = 'Received invalid data format for segments';
            }
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch segments';
            console.error('Error fetching segments:', err);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Create new segment
     */
    async createSegment(name: string, query: Record<string, any>): Promise<Segment | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.isLoading = true;
        this.error = null;

        try {
            const segment = await segmentsAPI.createSegment(accountId, { name, query });
            this.addOrUpdateSegment(segment);
            return segment;
        } catch (err: any) {
            this.error = err.message || 'Failed to create segment';
            console.error('Error creating segment:', err);
            return null;
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Add or update segment (used by WebSocket events or creation)
     */
    addOrUpdateSegment(segment: Segment): void {
        const index = this.allSegments.findIndex((s) => s.id === segment.id);
        if (index !== -1) {
            this.allSegments[index] = segment;
            // Trigger reactivity if needed, but svelte 5 fine-grained reactivity handles array mutation if it was assign? 
            // Array mutation on $state array works if using push/pop etc through proxy, or reassign.
            // this.allSegments[index] = segment is fine.
        } else {
            this.allSegments.push(segment);
        }
    }

    /**
     * Remove segment (used by WebSocket events or deletion)
     */
    removeSegment(segmentId: number): void {
        this.allSegments = this.allSegments.filter((s) => s.id !== segmentId);
    }

    /**
     * Reset store to initial state
     */
    reset(): void {
        this.allSegments = [];
        this.isLoading = false;
        this.error = null;
    }
}

// Export singleton instance
export const segmentsStore = new SegmentsStore();
