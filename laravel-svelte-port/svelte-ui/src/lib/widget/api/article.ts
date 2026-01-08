/**
 * Article API
 * 
 * API methods for help articles in widget.
 */

import { getWidgetApi } from './client';
import type { Article, ApiResponse } from './types';

/**
 * Search articles
 */
export async function searchArticles(query: string): Promise<Article[]> {
  const api = getWidgetApi();
  const response = await api
    .get('articles/search', { searchParams: { query } })
    .json<ApiResponse<Article[]>>();
  return response.data;
}

/**
 * Get article by slug
 */
export async function getArticle(slug: string): Promise<Article> {
  const api = getWidgetApi();
  const response = await api.get(`articles/${slug}`).json<ApiResponse<Article>>();
  return response.data;
}
