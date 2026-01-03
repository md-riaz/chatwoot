/**
 * Widget Articles Store
 * 
 * Manages help articles search and display.
 */

import * as articleApi from '../api/article';
import type { Article } from '../api/types';

class WidgetArticlesStore {
  private articles = $state<Article[]>([]);
  private selectedArticle = $state<Article | null>(null);
  private searchQuery = $state('');
  private isLoading = $state(false);

  // Getters
  get allArticles() {
    return this.articles;
  }

  get selected() {
    return this.selectedArticle;
  }

  get query() {
    return this.searchQuery;
  }

  get loading() {
    return this.isLoading;
  }

  // Derived values
  get hasArticles() {
    return (this.articles.length > 0);
  }

  get hasSelection() {
    return (!!this.selectedArticle);
  }

  // Actions
  async searchArticles(query: string): Promise<void> {
    this.searchQuery = query;
    this.isLoading = true;

    try {
      const results = await articleApi.searchArticles(query);
      this.articles = results;
    } catch (err) {
      console.error('Failed to search articles:', err);
      this.articles = [];
    } finally {
      this.isLoading = false;
    }
  }

  async loadArticle(slug: string): Promise<void> {
    this.isLoading = true;

    try {
      const article = await articleApi.getArticle(slug);
      this.selectedArticle = article;
    } catch (err) {
      console.error('Failed to load article:', err);
      this.selectedArticle = null;
    } finally {
      this.isLoading = false;
    }
  }

  selectArticle(article: Article) {
    this.selectedArticle = article;
  }

  clearSelection() {
    this.selectedArticle = null;
  }

  clearSearch() {
    this.articles = [];
    this.searchQuery = '';
  }

  reset() {
    this.articles = [];
    this.selectedArticle = null;
    this.searchQuery = '';
    this.isLoading = false;
  }
}

export const widgetArticlesStore = new WidgetArticlesStore();
