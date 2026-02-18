import { api, toSearchParams } from './client';

export interface PortalCategory {
    id: number;
    name: string;
    slug: string;
    description: string;
    position: number;
    portal_id: number;
    locale: string;
}

export interface CategoryListParams {
    page?: number;
    perPage?: number;
    locale?: string;
}

export interface CreateCategoryParams {
    name: string;
    slug?: string;
    description?: string;
    position?: number;
    locale?: string;
    parent_category_id?: number;
}

export interface UpdateCategoryParams {
    name?: string;
    slug?: string;
    description?: string;
    position?: number;
    parent_category_id?: number;
}

/**
 * Get list of categories for a portal
 */
export async function getCategories(
    accountId: number,
    portalSlug: string,
    params?: CategoryListParams
): Promise<PortalCategory[]> {
    const response = await api.get(`api/v1/accounts/${accountId}/portals/${portalSlug}/categories`, {
        searchParams: toSearchParams(params),
    }).json<{ payload: PortalCategory[] }>();
    return response.payload;
}

/**
 * Get single category
 */
export async function getCategory(
    accountId: number,
    portalSlug: string,
    categoryId: number // or slug? usually ID in nested resources or slug if unique
): Promise<PortalCategory> {
    const response = await api.get(`api/v1/accounts/${accountId}/portals/${portalSlug}/categories/${categoryId}`).json<{ payload: PortalCategory }>();
    return response.payload;
}

/**
 * Create new category
 */
export async function createCategory(
    accountId: number,
    portalSlug: string,
    params: CreateCategoryParams
): Promise<PortalCategory> {
    const response = await api.post(`api/v1/accounts/${accountId}/portals/${portalSlug}/categories`, {
        json: params,
    }).json<{ payload: PortalCategory }>();
    return response.payload;
}

/**
 * Update category
 */
export async function updateCategory(
    accountId: number,
    portalSlug: string,
    categoryId: number,
    params: UpdateCategoryParams
): Promise<PortalCategory> {
    const response = await api.patch(`api/v1/accounts/${accountId}/portals/${portalSlug}/categories/${categoryId}`, {
        json: params,
    }).json<{ payload: PortalCategory }>();
    return response.payload;
}

/**
 * Delete category
 */
export async function deleteCategory(
    accountId: number,
    portalSlug: string,
    categoryId: number
): Promise<void> {
    await api.delete(`api/v1/accounts/${accountId}/portals/${portalSlug}/categories/${categoryId}`);
}
