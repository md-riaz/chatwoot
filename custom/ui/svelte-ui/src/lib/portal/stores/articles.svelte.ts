/**
 * Portal Articles Store
 * 
 * Manages portal articles state.
 */

import * as articlesApi from '../api/articles';
import type { Article, ArticleSearchResult } from '../api/types';

class PortalArticlesStore {
  private articles = $state<Article[]>([]);
  private searchResults = $state<ArticleSearchResult[]>([]);
  private selectedArticle = $state<Article | null>(null);
  private searchQuery = $state('');
  private isLoading = $state(false);
  private isSearching = $state(false);
  private error = $state<string | null>(null);

  // Getters
  get allArticles() {
    return this.articles;
  }

  get results() {
    return this.searchResults;
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

  get searching() {
    return this.isSearching;
  }

  get errorMessage() {
    return this.error;
  }

  // Derived values
  get hasArticles() {
    return $derived(this.articles.length > 0);
  }

  get hasSearchResults() {
    return $derived(this.searchResults.length > 0);
  }

  get hasSelection() {
    return $derived(!!this.selectedArticle);
  }

  // Actions
  async searchArticles(query: string, locale?: string): Promise<void> {
    this.searchQuery = query;
    this.isSearching = true;
    this.error = null;

    try {
      const results = await articlesApi.searchArticles(query, locale);
      this.searchResults = results;
    } catch (err: any) {
      this.error = err.message || 'Failed to search articles';
      this.searchResults = [];
    } finally {
      this.isSearching = false;
    }
  }

  async fetchArticle(slug: string, locale?: string): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const article = await articlesApi.getArticle(slug, locale);
      this.selectedArticle = article;

      // Track view
      await articlesApi.trackArticleView(article.id);
    } catch (err: any) {
      this.error = err.message || 'Failed to load article';
      this.selectedArticle = null;
    } finally {
      this.isLoading = false;
    }
  }

  async fetchArticlesByCategory(categorySlug: string, locale?: string): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const articles = await articlesApi.getArticlesByCategory(categorySlug, locale);
      this.articles = articles;
    } catch (err: any) {
      this.error = err.message || 'Failed to load articles';
      this.articles = [];
    } finally {
      this.isLoading = false;
    }
  }

  async submitFeedback(articleId: number, helpful: boolean, feedback?: string): Promise<void> {
    try {
      await articlesApi.submitArticleFeedback({ articleId, helpful, feedback });
    } catch (err: any) {
      console.error('Failed to submit feedback:', err);
    }
  }

  selectArticle(article: Article) {
    this.selectedArticle = article;
  }

  clearSelection() {
    this.selectedArticle = null;
  }

  clearSearch() {
    this.searchResults = [];
    this.searchQuery = '';
  }

  reset() {
    this.articles = [];
    this.searchResults = [];
    this.selectedArticle = null;
    this.searchQuery = '';
    this.isLoading = false;
    this.isSearching = false;
    this.error = null;
  }
}

export const portalArticlesStore = new PortalArticlesStore();
