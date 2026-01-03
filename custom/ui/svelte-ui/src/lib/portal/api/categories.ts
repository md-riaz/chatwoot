/**
 * Portal Categories API
 * 
 * API methods for portal categories.
 */

import { getPortalApi } from './client';
import type { Category, ApiResponse } from './types';

/**
 * Get all categories
 */
export async function getCategories(locale?: string): Promise<Category[]> {
  const api = getPortalApi();
  const searchParams = locale ? { locale } : {};

  const response = await api
    .get('categories', { searchParams })
    .json<ApiResponse<Category[]>>();
  return response.data;
}

/**
 * Get category by slug
 */
export async function getCategory(slug: string, locale?: string): Promise<Category> {
  const api = getPortalApi();
  const searchParams = locale ? { locale } : {};

  const response = await api
    .get(`categories/${slug}`, { searchParams })
    .json<ApiResponse<Category>>();
  return response.data;
}

/**
 * Get subcategories
 */
export async function getSubcategories(
  parentSlug: string,
  locale?: string
): Promise<Category[]> {
  const api = getPortalApi();
  const searchParams = locale ? { locale } : {};

  const response = await api
    .get(`categories/${parentSlug}/subcategories`, { searchParams })
    .json<ApiResponse<Category[]>>();
  return response.data;
}
