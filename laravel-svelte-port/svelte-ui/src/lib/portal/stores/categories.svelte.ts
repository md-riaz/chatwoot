/**
 * Portal Categories Store
 * 
 * Manages portal categories state.
 */

import * as categoriesApi from '../api/categories';
import type { Category } from '../api/types';

class PortalCategoriesStore {
  private categories = $state<Category[]>([]);
  private selectedCategory = $state<Category | null>(null);
  private isLoading = $state(false);
  private error = $state<string | null>(null);

  // Getters
  get allCategories() {
    return this.categories;
  }

  get selected() {
    return this.selectedCategory;
  }

  get loading() {
    return this.isLoading;
  }

  get errorMessage() {
    return this.error;
  }

  // Derived values
  get topLevelCategories() {
    return (this.categories.filter((c) => !c.parentCategoryId));
  }

  get hasCategories() {
    return (this.categories.length > 0);
  }

  // Actions
  async fetchCategories(locale?: string): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const categories = await categoriesApi.getCategories(locale);
      this.categories = categories;
    } catch (err: any) {
      this.error = err.message || 'Failed to load categories';
    } finally {
      this.isLoading = false;
    }
  }

  async fetchCategory(slug: string, locale?: string): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const category = await categoriesApi.getCategory(slug, locale);
      this.selectedCategory = category;
    } catch (err: any) {
      this.error = err.message || 'Failed to load category';
    } finally {
      this.isLoading = false;
    }
  }

  async fetchSubcategories(parentSlug: string, locale?: string): Promise<Category[]> {
    try {
      return await categoriesApi.getSubcategories(parentSlug, locale);
    } catch (err: any) {
      this.error = err.message || 'Failed to load subcategories';
      return [];
    }
  }

  selectCategory(category: Category) {
    this.selectedCategory = category;
  }

  clearSelection() {
    this.selectedCategory = null;
  }

  reset() {
    this.categories = [];
    this.selectedCategory = null;
    this.isLoading = false;
    this.error = null;
  }
}

export const portalCategoriesStore = new PortalCategoriesStore();
