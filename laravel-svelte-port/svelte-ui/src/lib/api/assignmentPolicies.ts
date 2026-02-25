import { api } from './client';

/**
 * Assignment Policy interfaces
 */
export interface AssignmentPolicy {
    id: number;
    name: string;
    description: string;
    assignmentOrder: string;
    conversationPriority: string;
    fairDistributionLimit: number;
    fairDistributionWindow: number;
    enabled: boolean;
    assignedInboxCount: number;
    inboxes?: PolicyInbox[];
}

export interface PolicyInbox {
    id: number;
    name: string;
    channelType?: string;
    medium?: string;
    email?: string;
    phoneNumber?: string;
}

export interface CreateAssignmentPolicyParams {
    name: string;
    description?: string;
    assignmentOrder?: string;
    conversationPriority?: string;
    fairDistributionLimit?: number;
    fairDistributionWindow?: number;
    enabled?: boolean;
}

export interface UpdateAssignmentPolicyParams {
    name?: string;
    description?: string;
    assignmentOrder?: string;
    conversationPriority?: string;
    fairDistributionLimit?: number;
    fairDistributionWindow?: number;
    enabled?: boolean;
}

/**
 * Get all assignment policies for the account
 */
export async function getAssignmentPolicies(
    accountId: number
): Promise<AssignmentPolicy[]> {
    const response = await api
        .get(`api/v1/accounts/${accountId}/assignment_policies`)
        .json<{ data: AssignmentPolicy[] }>();
    return response.data;
}

/**
 * Get a single assignment policy
 */
export async function getAssignmentPolicy(
    accountId: number,
    policyId: number
): Promise<AssignmentPolicy> {
    const response = await api
        .get(`api/v1/accounts/${accountId}/assignment_policies/${policyId}`)
        .json<{ data: AssignmentPolicy }>();
    return response.data;
}

/**
 * Create a new assignment policy
 */
export async function createAssignmentPolicy(
    accountId: number,
    data: CreateAssignmentPolicyParams
): Promise<AssignmentPolicy> {
    const response = await api
        .post(`api/v1/accounts/${accountId}/assignment_policies`, { json: data })
        .json<{ data: AssignmentPolicy }>();
    return response.data;
}

/**
 * Update an assignment policy
 */
export async function updateAssignmentPolicy(
    accountId: number,
    policyId: number,
    data: UpdateAssignmentPolicyParams
): Promise<AssignmentPolicy> {
    const response = await api
        .patch(`api/v1/accounts/${accountId}/assignment_policies/${policyId}`, {
            json: data,
        })
        .json<{ data: AssignmentPolicy }>();
    return response.data;
}

/**
 * Delete an assignment policy
 */
export async function deleteAssignmentPolicy(
    accountId: number,
    policyId: number
): Promise<void> {
    await api
        .delete(`api/v1/accounts/${accountId}/assignment_policies/${policyId}`)
        .json();
}

/**
 * Get inboxes associated with a policy
 */
export async function getPolicyInboxes(
    accountId: number,
    policyId: number
): Promise<PolicyInbox[]> {
    const response = await api
        .get(`api/v1/accounts/${accountId}/assignment_policies/${policyId}/inboxes`)
        .json<{ data: PolicyInbox[] }>();
    return response.data;
}

/**
 * Associate an inbox with a policy
 */
export async function addPolicyInbox(
    accountId: number,
    policyId: number,
    inboxId: number
): Promise<void> {
    await api
        .post(`api/v1/accounts/${accountId}/assignment_policies/${policyId}/inboxes`, {
            json: { inboxId },
        })
        .json();
}

/**
 * Remove an inbox from a policy
 */
export async function removePolicyInbox(
    accountId: number,
    policyId: number,
    inboxId: number
): Promise<void> {
    await api
        .delete(`api/v1/accounts/${accountId}/assignment_policies/${policyId}/inboxes`, {
            json: { inboxId },
        })
        .json();
}
