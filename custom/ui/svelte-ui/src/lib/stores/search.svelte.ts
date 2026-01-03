/**
 * Search Store
 * Manages search state and history using Svelte 5 runes
 */

import * as searchApi from '$lib/api/search';
import type { SearchResult, SearchFilters } from '$lib/api/search';

interface SearchState {
  query: string;
  results: SearchResult[];
  filters: SearchFilters;
  isSearching: boolean;
  error: string | null;
  currentPage: number;
  totalResults: number;
  history: string[];
}

const MAX_HISTORY = 10;

class SearchStore {
  private state = $state<SearchState>({
    query: '',
    results: [],
    filters: {},
    isSearching: false,
    error: null,
    currentPage: 1,
    totalResults: 0,
    history: this.loadHistory()
  });

  // Getters
  get query() {
    return this.state.query;
  }

  get results() {
    return this.state.results;
  }

  get filters() {
    return this.state.filters;
  }

  get isSearching() {
    return this.state.isSearching;
  }

  get error() {
    return this.state.error;
  }

  get history() {
    return this.state.history;
  }

  get totalResults() {
    return this.state.totalResults;
  }

  // Derived getters
  get conversationResults() {
    return $derived(
      this.state.results.filter(r => r.type === 'conversation')
    );
  }

  get contactResults() {
    return $derived(
      this.state.results.filter(r => r.type === 'contact')
    );
  }

  get messageResults() {
    return $derived(
      this.state.results.filter(r => r.type === 'message')
    );
  }

  get hasResults() {
    return $derived(this.state.results.length > 0);
  }

  get hasQuery() {
    return $derived(this.state.query.trim().length > 0);
  }

  // Actions
  async performSearch(query: string, filters: SearchFilters = {}, page: number = 1) {
    if (!query.trim()) {
      this.clearResults();
      return;
    }

    this.state.query = query;
    this.state.filters = filters;
    this.state.isSearching = true;
    this.state.error = null;
    this.state.currentPage = page;

    try {
      const response = await searchApi.search(query, filters, page);
      
      if (page === 1) {
        this.state.results = response.results;
      } else {
        this.state.results = [...this.state.results, ...response.results];
      }
      
      this.state.totalResults = response.meta.total;
      
      // Add to history
      this.addToHistory(query);
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Search failed';
      console.error('Search error:', error);
    } finally {
      this.state.isSearching = false;
    }
  }

  async searchConversations(query: string, filters: Omit<SearchFilters, 'type'> = {}) {
    await this.performSearch(query, { ...filters, type: 'conversation' });
  }

  async searchContacts(query: string) {
    await this.performSearch(query, { type: 'contact' });
  }

  async searchMessages(query: string, filters: Omit<SearchFilters, 'type'> = {}) {
    await this.performSearch(query, { ...filters, type: 'message' });
  }

  async loadMore() {
    if (this.state.isSearching || !this.state.query) return;
    
    await this.performSearch(
      this.state.query,
      this.state.filters,
      this.state.currentPage + 1
    );
  }

  setQuery(query: string) {
    this.state.query = query;
  }

  setFilters(filters: SearchFilters) {
    this.state.filters = filters;
  }

  clearResults() {
    this.state.results = [];
    this.state.totalResults = 0;
    this.state.currentPage = 1;
  }

  clearQuery() {
    this.state.query = '';
    this.clearResults();
  }

  clearError() {
    this.state.error = null;
  }

  // History management
  private loadHistory(): string[] {
    if (typeof localStorage === 'undefined') return [];
    
    try {
      const saved = localStorage.getItem('search_history');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  }

  private saveHistory() {
    if (typeof localStorage === 'undefined') return;
    
    try {
      localStorage.setItem('search_history', JSON.stringify(this.state.history));
    } catch (error) {
      console.error('Failed to save search history:', error);
    }
  }

  addToHistory(query: string) {
    const trimmed = query.trim();
    if (!trimmed || this.state.history.includes(trimmed)) return;
    
    this.state.history = [trimmed, ...this.state.history].slice(0, MAX_HISTORY);
    this.saveHistory();
  }

  removeFromHistory(query: string) {
    this.state.history = this.state.history.filter(q => q !== query);
    this.saveHistory();
  }

  clearHistory() {
    this.state.history = [];
    this.saveHistory();
  }

  reset() {
    this.state = {
      query: '',
      results: [],
      filters: {},
      isSearching: false,
      error: null,
      currentPage: 1,
      totalResults: 0,
      history: this.loadHistory()
    };
  }
}

export const searchStore = new SearchStore();
