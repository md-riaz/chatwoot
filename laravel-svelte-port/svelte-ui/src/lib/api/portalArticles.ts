import { api, toSearchParams } from './client';

export interface PortalArticle {
    id: number;
    title: string;
    slug: string;
    content: string;
    description: string;
    position: number;
    portal_id: number;
    category_id: number;
    author_id: number;
    status: 'draft' | 'published' | 'archived';
    views: number;
}

export interface ArticleListParams {
    page?: number;
    perPage?: number;
    category_id?: number;
    status?: string;
    locale?: string;
}

export interface CreateArticleParams {
    title: string;
    slug?: string;
    content: string;
    description?: string;
    category_id: number;
    position?: number;
    status?: 'draft' | 'published';
    author_id?: number;
}

export interface UpdateArticleParams {
    title?: string;
    slug?: string;
    content?: string;
    description?: string;
    category_id?: number;
    position?: number;
    status?: 'draft' | 'published' | 'archived';
    author_id?: number;
}

/**
 * Get list of articles for a portal
 */
export async function getArticles(
    accountId: number,
    portalSlug: string,
    params?: ArticleListParams
): Promise<PortalArticle[]> {
    const response = await api.get(`api/v1/accounts/${accountId}/portals/${portalSlug}/articles`, {
        searchParams: toSearchParams(params),
    }).json<{ payload: PortalArticle[] }>();
    return response.payload;
}

/**
 * Get single article
 */
export async function getArticle(
    accountId: number,
    portalSlug: string,
    articleId: number
): Promise<PortalArticle> {
    const response = await api.get(`api/v1/accounts/${accountId}/portals/${portalSlug}/articles/${articleId}`).json<{ payload: PortalArticle }>();
    return response.payload;
}

/**
 * Create new article
 */
export async function createArticle(
    accountId: number,
    portalSlug: string,
    params: CreateArticleParams
): Promise<PortalArticle> {
    const response = await api.post(`api/v1/accounts/${accountId}/portals/${portalSlug}/articles`, {
        json: params,
    }).json<{ payload: PortalArticle }>();
    return response.payload;
}

/**
 * Update article
 */
export async function updateArticle(
    accountId: number,
    portalSlug: string,
    articleId: number,
    params: UpdateArticleParams
): Promise<PortalArticle> {
    const response = await api.patch(`api/v1/accounts/${accountId}/portals/${portalSlug}/articles/${articleId}`, {
        json: params,
    }).json<{ payload: PortalArticle }>();
    return response.payload;
}

/**
 * Delete article
 */
export async function deleteArticle(
    accountId: number,
    portalSlug: string,
    articleId: number
): Promise<void> {
    await api.delete(`api/v1/accounts/${accountId}/portals/${portalSlug}/articles/${articleId}`);
}
