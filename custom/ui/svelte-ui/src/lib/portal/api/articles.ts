/**
 * Portal Articles API
 * 
 * API methods for portal articles.
 */

import { getPortalApi } from './client';
import type { Article, ArticleSearchResult, ArticleFeedback, ApiResponse } from './types';

/**
 * Search articles
 */
export async function searchArticles(
  query: string,
  locale?: string
): Promise<ArticleSearchResult[]> {
  const api = getPortalApi();
  const searchParams: Record<string, string> = { query };
  if (locale) searchParams.locale = locale;

  const response = await api
    .get('articles/search', { searchParams })
    .json<ApiResponse<ArticleSearchResult[]>>();
  return response.data;
}

/**
 * Get article by slug
 */
export async function getArticle(slug: string, locale?: string): Promise<Article> {
  const api = getPortalApi();
  const searchParams = locale ? { locale } : {};

  const response = await api
    .get(`articles/${slug}`, { searchParams })
    .json<ApiResponse<Article>>();
  return response.data;
}

/**
 * Get articles by category
 */
export async function getArticlesByCategory(
  categorySlug: string,
  locale?: string
): Promise<Article[]> {
  const api = getPortalApi();
  const searchParams = locale ? { locale } : {};

  const response = await api
    .get(`categories/${categorySlug}/articles`, { searchParams })
    .json<ApiResponse<Article[]>>();
  return response.data;
}

/**
 * Submit article feedback
 */
export async function submitArticleFeedback(feedback: ArticleFeedback): Promise<void> {
  const api = getPortalApi();
  await api.post('articles/feedback', { json: feedback }).json();
}

/**
 * Increment article view count
 */
export async function trackArticleView(articleId: number): Promise<void> {
  const api = getPortalApi();
  await api.post(`articles/${articleId}/views`).json();
}
