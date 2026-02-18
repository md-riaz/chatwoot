import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as categoriesAPI from '$lib/api/portalCategories';
import type {
    PortalCategory,
    CreateCategoryParams,
    UpdateCategoryParams,
} from '$lib/api/portalCategories';

class PortalCategoriesStore {
    // Reactive state
    allCategories = $state<PortalCategory[]>([]);
    selectedCategoryId = $state<number | null>(null);

    error = $state<string | null>(null);

    uiFlags = $state({
        isFetching: false,
        isCreating: false,
        isUpdating: false,
        isDeleting: false,
    });

    // Computed
    selectedCategory = $derived(
        this.allCategories.find(c => c.id === this.selectedCategoryId) || null
    );

    get currentAccountId(): number {
        const pageStore = get(page);
        return Number(pageStore.params.accountId);
    }

    async fetchCategories(portalSlug: string, locale?: string): Promise<void> {
        const accountId = this.currentAccountId;
        if (!accountId) return;

        this.uiFlags.isFetching = true;
        this.error = null;

        try {
            const categories = await categoriesAPI.getCategories(accountId, portalSlug, { locale });
            this.allCategories = categories;
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch categories';
            console.error('Error fetching categories:', err);
        } finally {
            this.uiFlags.isFetching = false;
        }
    }

    async createCategory(portalSlug: string, data: CreateCategoryParams): Promise<PortalCategory | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isCreating = true;
        this.error = null;

        try {
            const category = await categoriesAPI.createCategory(accountId, portalSlug, data);
            this.allCategories = [...this.allCategories, category];
            return category;
        } catch (err: any) {
            this.error = err.message || 'Failed to create category';
            console.error('Error creating category:', err);
            return null;
        } finally {
            this.uiFlags.isCreating = false;
        }
    }

    async updateCategory(portalSlug: string, categoryId: number, data: UpdateCategoryParams): Promise<PortalCategory | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isUpdating = true;
        this.error = null;

        try {
            const updatedCategory = await categoriesAPI.updateCategory(accountId, portalSlug, categoryId, data);

            const index = this.allCategories.findIndex(c => c.id === categoryId);
            if (index !== -1) {
                this.allCategories[index] = updatedCategory;
            }
            return updatedCategory;
        } catch (err: any) {
            this.error = err.message || 'Failed to update category';
            console.error('Error updating category:', err);
            return null;
        } finally {
            this.uiFlags.isUpdating = false;
        }
    }

    async deleteCategory(portalSlug: string, categoryId: number): Promise<boolean> {
        const accountId = this.currentAccountId;
        if (!accountId) return false;

        this.uiFlags.isDeleting = true;
        this.error = null;

        const previousCategories = this.allCategories;
        this.allCategories = this.allCategories.filter(c => c.id !== categoryId);

        try {
            await categoriesAPI.deleteCategory(accountId, portalSlug, categoryId);
            return true;
        } catch (err: any) {
            this.allCategories = previousCategories;
            this.error = err.message || 'Failed to delete category';
            console.error('Error deleting category:', err);
            return false;
        } finally {
            this.uiFlags.isDeleting = false;
        }
    }

    selectCategory(id: number | null): void {
        this.selectedCategoryId = id;
    }
}

export const portalCategoriesStore = new PortalCategoriesStore();
