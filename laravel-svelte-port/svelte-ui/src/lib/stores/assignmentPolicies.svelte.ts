import { page } from '$app/state';
import * as assignmentPoliciesApi from '$lib/api/assignmentPolicies';
import type {
    AssignmentPolicy,
    PolicyInbox,
    CreateAssignmentPolicyParams,
    UpdateAssignmentPolicyParams,
} from '$lib/api/assignmentPolicies';

/**
 * Assignment Policies Store using Svelte 5 runes
 * Manages assignment policy state and CRUD operations
 */
class AssignmentPoliciesStore {
    // Reactive state
    records = $state<AssignmentPolicy[]>([]);
    isLoading = $state(false);
    isFetchingItem = $state(false);
    isCreating = $state(false);
    isUpdating = $state(false);
    isDeleting = $state(false);
    isFetchingInboxes = $state(false);
    error = $state<string | null>(null);

    // Computed account ID from route params
    get currentAccountId(): number {
        return parseInt(page.params.accountId || '0', 10);
    }

    /**
     * Get a policy by ID
     */
    getPolicyById(id: number | string): AssignmentPolicy | undefined {
        return this.records.find((r) => r.id === Number(id));
    }

    /**
     * Fetch all assignment policies
     */
    async fetchAll(): Promise<void> {
        if (!this.currentAccountId) return;

        try {
            this.isLoading = true;
            this.error = null;
            const policies = await assignmentPoliciesApi.getAssignmentPolicies(
                this.currentAccountId
            );
            this.records = policies || [];
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch assignment policies';
            console.error('Error fetching assignment policies:', err);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Fetch a single assignment policy
     */
    async fetch(policyId: number): Promise<AssignmentPolicy | null> {
        if (!this.currentAccountId) return null;

        try {
            this.isFetchingItem = true;
            this.error = null;
            const policy = await assignmentPoliciesApi.getAssignmentPolicy(
                this.currentAccountId,
                policyId
            );

            // Update or add in local records
            const index = this.records.findIndex((r) => r.id === policy.id);
            if (index !== -1) {
                this.records[index] = policy;
            } else {
                this.records.push(policy);
            }

            return policy;
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch assignment policy';
            console.error('Error fetching assignment policy:', err);
            return null;
        } finally {
            this.isFetchingItem = false;
        }
    }

    /**
     * Create a new assignment policy
     */
    async create(
        data: CreateAssignmentPolicyParams
    ): Promise<AssignmentPolicy | null> {
        if (!this.currentAccountId) return null;

        try {
            this.isCreating = true;
            this.error = null;
            const policy = await assignmentPoliciesApi.createAssignmentPolicy(
                this.currentAccountId,
                data
            );
            this.records.push(policy);
            return policy;
        } catch (err: any) {
            this.error = err.message || 'Failed to create assignment policy';
            console.error('Error creating assignment policy:', err);
            throw err;
        } finally {
            this.isCreating = false;
        }
    }

    /**
     * Update an existing assignment policy
     */
    async update(
        policyId: number,
        data: UpdateAssignmentPolicyParams
    ): Promise<AssignmentPolicy | null> {
        if (!this.currentAccountId) return null;

        try {
            this.isUpdating = true;
            this.error = null;
            const policy = await assignmentPoliciesApi.updateAssignmentPolicy(
                this.currentAccountId,
                policyId,
                data
            );

            const index = this.records.findIndex((r) => r.id === policyId);
            if (index !== -1) {
                this.records[index] = policy;
            }

            return policy;
        } catch (err: any) {
            this.error = err.message || 'Failed to update assignment policy';
            console.error('Error updating assignment policy:', err);
            throw err;
        } finally {
            this.isUpdating = false;
        }
    }

    /**
     * Delete an assignment policy
     */
    async deletePolicy(policyId: number): Promise<boolean> {
        if (!this.currentAccountId) return false;

        try {
            this.isDeleting = true;
            this.error = null;
            await assignmentPoliciesApi.deleteAssignmentPolicy(
                this.currentAccountId,
                policyId
            );
            this.records = this.records.filter((r) => r.id !== policyId);
            return true;
        } catch (err: any) {
            this.error = err.message || 'Failed to delete assignment policy';
            console.error('Error deleting assignment policy:', err);
            throw err;
        } finally {
            this.isDeleting = false;
        }
    }

    /**
     * Fetch inboxes for a policy
     */
    async fetchInboxes(policyId: number): Promise<void> {
        if (!this.currentAccountId) return;

        try {
            this.isFetchingInboxes = true;
            const inboxes = await assignmentPoliciesApi.getPolicyInboxes(
                this.currentAccountId,
                policyId
            );

            const policy = this.records.find((r) => r.id === policyId);
            if (policy) {
                policy.inboxes = inboxes;
            }
        } catch (err: any) {
            console.error('Error fetching policy inboxes:', err);
        } finally {
            this.isFetchingInboxes = false;
        }
    }

    /**
     * Add an inbox to a policy
     */
    async addInbox(policyId: number, inboxId: number): Promise<boolean> {
        if (!this.currentAccountId) return false;

        try {
            await assignmentPoliciesApi.addPolicyInbox(
                this.currentAccountId,
                policyId,
                inboxId
            );
            // Refresh inboxes after adding
            await this.fetchInboxes(policyId);
            return true;
        } catch (err: any) {
            console.error('Error adding inbox to policy:', err);
            throw err;
        }
    }

    /**
     * Remove an inbox from a policy
     */
    async removeInbox(policyId: number, inboxId: number): Promise<boolean> {
        if (!this.currentAccountId) return false;

        try {
            await assignmentPoliciesApi.removePolicyInbox(
                this.currentAccountId,
                policyId,
                inboxId
            );

            // Remove from local state
            const policy = this.records.find((r) => r.id === policyId);
            if (policy?.inboxes) {
                policy.inboxes = policy.inboxes.filter((i) => i.id !== inboxId);
            }
            return true;
        } catch (err: any) {
            console.error('Error removing inbox from policy:', err);
            throw err;
        }
    }

    /**
     * Clear all records
     */
    clear(): void {
        this.records = [];
        this.error = null;
    }
}

// Export singleton instance
export const assignmentPoliciesStore = new AssignmentPoliciesStore();
