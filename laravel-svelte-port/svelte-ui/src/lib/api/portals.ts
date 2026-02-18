import { api, toSearchParams } from './client';

export interface Portal {
    id: number;
    slug: string;
    name: string;
    color: string;
    custom_domain: string;
    header_text: string;
    homepage_link: string;
    page_title: string;
    config: Record<string, unknown>;
    archived: boolean;
    members: Array<{ user_id: number }>;
}

export interface PortalListParams {
    page?: number;
    perPage?: number;
}

export interface CreatePortalParams {
    name: string;
    slug: string;
    color?: string;
    custom_domain?: string;
    header_text?: string;
    homepage_link?: string;
    page_title?: string;
    config?: Record<string, unknown>;
}

export interface UpdatePortalParams {
    name?: string;
    slug?: string;
    color?: string;
    custom_domain?: string;
    header_text?: string;
    homepage_link?: string;
    page_title?: string;
    config?: Record<string, unknown>;
    archived?: boolean;
}

/**
 * Get list of portals
 */
export async function getPortals(accountId: number, params?: PortalListParams): Promise<Portal[]> {
    const response = await api.get(`api/v1/accounts/${accountId}/portals`, {
        searchParams: toSearchParams(params),
    }).json<{ payload: Portal[] }>();
    return response.payload;
}

/**
 * Get single portal by Slug
 */
export async function getPortal(accountId: number, slug: string): Promise<Portal> {
    const response = await api.get(`api/v1/accounts/${accountId}/portals/${slug}`).json<{ payload: Portal }>();
    return response.payload;
}

/**
 * Create new portal
 */
export async function createPortal(accountId: number, params: CreatePortalParams): Promise<Portal> {
    const response = await api.post(`api/v1/accounts/${accountId}/portals`, {
        json: params,
    }).json<{ payload: Portal }>();
    return response.payload;
}

/**
 * Update portal
 */
export async function updatePortal(accountId: number, slug: string, params: UpdatePortalParams): Promise<Portal> {
    const response = await api.patch(`api/v1/accounts/${accountId}/portals/${slug}`, {
        json: params,
    }).json<{ payload: Portal }>();
    return response.payload;
}

/**
 * Delete portal
 */
export async function deletePortal(accountId: number, slug: string): Promise<void> {
    await api.delete(`api/v1/accounts/${accountId}/portals/${slug}`);
}

/**
 * Add members to portal
 */
export async function addPortalMembers(accountId: number, slug: string, userIds: number[]): Promise<void> {
    await api.post(`api/v1/accounts/${accountId}/portals/${slug}/members`, {
        json: { user_ids: userIds }
    });
}
