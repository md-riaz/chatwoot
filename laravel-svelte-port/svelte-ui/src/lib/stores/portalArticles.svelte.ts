import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as articlesAPI from '$lib/api/portalArticles';
import type {
    PortalArticle,
    CreateArticleParams,
    UpdateArticleParams,
    ArticleListParams,
} from '$lib/api/portalArticles';

class PortalArticlesStore {
    // Reactive state
    allArticles = $state<PortalArticle[]>([]);
    selectedArticleId = $state<number | null>(null);

    error = $state<string | null>(null);
    meta = $state({
        page: 1,
        perPage: 15,
        total: 0,
    });

    uiFlags = $state({
        isFetching: false,
        isFetchingItem: false,
        isCreating: false,
        isUpdating: false,
        isDeleting: false,
    });

    // Computed
    selectedArticle = $derived(
        this.allArticles.find(a => a.id === this.selectedArticleId) || null
    );

    get currentAccountId(): number {
        const pageStore = get(page);
        return Number(pageStore.params.accountId);
    }

    async fetchArticles(portalSlug: string, params?: ArticleListParams): Promise<void> {
        const accountId = this.currentAccountId;
        if (!accountId) return;

        this.uiFlags.isFetching = true;
        this.error = null;

        try {
            const articles = await articlesAPI.getArticles(accountId, portalSlug, params);
            this.allArticles = articles;
            // Note: If pagination metadata is returned in headers or separate key, we should update meta here.
            // Current API client implementation returns pure array for payload.
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch articles';
            console.error('Error fetching articles:', err);
        } finally {
            this.uiFlags.isFetching = false;
        }
    }

    async fetchArticle(portalSlug: string, articleId: number): Promise<void> {
        const accountId = this.currentAccountId;
        if (!accountId) return;

        this.uiFlags.isFetchingItem = true;
        this.error = null;

        try {
            const article = await articlesAPI.getArticle(accountId, portalSlug, articleId);
            // Update list or selected item
            const index = this.allArticles.findIndex(a => a.id === articleId);
            if (index !== -1) {
                this.allArticles[index] = article;
            } else {
                this.allArticles = [...this.allArticles, article];
            }
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch article';
            console.error('Error fetching article:', err);
        } finally {
            this.uiFlags.isFetchingItem = false;
        }
    }

    async createArticle(portalSlug: string, data: CreateArticleParams): Promise<PortalArticle | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isCreating = true;
        this.error = null;

        try {
            const article = await articlesAPI.createArticle(accountId, portalSlug, data);
            this.allArticles = [article, ...this.allArticles];
            return article;
        } catch (err: any) {
            this.error = err.message || 'Failed to create article';
            console.error('Error creating article:', err);
            return null;
        } finally {
            this.uiFlags.isCreating = false;
        }
    }

    async updateArticle(portalSlug: string, articleId: number, data: UpdateArticleParams): Promise<PortalArticle | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isUpdating = true;
        this.error = null;

        try {
            const updatedArticle = await articlesAPI.updateArticle(accountId, portalSlug, articleId, data);

            const index = this.allArticles.findIndex(a => a.id === articleId);
            if (index !== -1) {
                this.allArticles[index] = updatedArticle;
            }
            return updatedArticle;
        } catch (err: any) {
            this.error = err.message || 'Failed to update article';
            console.error('Error updating article:', err);
            return null;
        } finally {
            this.uiFlags.isUpdating = false;
        }
    }

    async deleteArticle(portalSlug: string, articleId: number): Promise<boolean> {
        const accountId = this.currentAccountId;
        if (!accountId) return false;

        this.uiFlags.isDeleting = true;
        this.error = null;

        const previousArticles = this.allArticles;
        this.allArticles = this.allArticles.filter(a => a.id !== articleId);

        try {
            await articlesAPI.deleteArticle(accountId, portalSlug, articleId);
            return true;
        } catch (err: any) {
            this.allArticles = previousArticles;
            this.error = err.message || 'Failed to delete article';
            console.error('Error deleting article:', err);
            return false;
        } finally {
            this.uiFlags.isDeleting = false;
        }
    }

    selectArticle(id: number | null): void {
        this.selectedArticleId = id;
    }
}

export const portalArticlesStore = new PortalArticlesStore();
