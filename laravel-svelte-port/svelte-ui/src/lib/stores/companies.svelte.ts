import { goto } from '$app/navigation';
import { page } from '$app/stores';
import * as companiesApi from '$lib/api/companies';
import type {
  Company,
  CreateCompanyParams,
  UpdateCompanyParams,
  CompanyListParams,
  CompanySearchParams,
} from '$lib/api/companies';
import { get } from 'svelte/store';

/**
 * Companies Store using Svelte 5 runes
 * Manages company state and CRUD operations
 */
class CompaniesStore {
  // Reactive state using $state rune
  allCompanies = $state<Company[]>([]);
  selectedCompanyId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  isCreating = $state<boolean>(false);
  isUpdating = $state<boolean>(false);
  isDeleting = $state<boolean>(false);
  error = $state<string | null>(null);
  searchQuery = $state<string>('');
  currentPage = $state<number>(1);
  hasMorePages = $state<boolean>(true);
  totalCount = $state<number>(0);

  // Computed values using $derived rune
  selectedCompany = $derived(
    this.allCompanies.find((c) => c.id === this.selectedCompanyId) || null
  );

  // Computed account ID from route params
  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  get sortedCompanies(): Company[] {
    return [...this.allCompanies].sort((a, b) => {
      const nameA = a.name?.toLowerCase() || '';
      const nameB = b.name?.toLowerCase() || '';
      return nameA.localeCompare(nameB);
    });
  }

  get filteredCompanies(): Company[] {
    let companies = this.sortedCompanies;

    // Filter by search query (client-side filtering for already loaded data)
    if (this.searchQuery.trim()) {
      const query = this.searchQuery.toLowerCase();
      companies = companies.filter(
        (company) =>
          company.name?.toLowerCase().includes(query) ||
          company.domain?.toLowerCase().includes(query) ||
          company.industry?.toLowerCase().includes(query)
      );
    }

    return companies;
  }

  get companiesCount(): number {
    return this.allCompanies.length;
  }

  /**
   * Fetch companies with pagination
   */
  async fetchCompanies(params: CompanyListParams = {}): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const response = await companiesApi.getCompanies(
        this.currentAccountId,
        params
      );

      this.allCompanies = response.data || [];
      this.currentPage = response.meta?.currentPage || 1;
      this.hasMorePages = !!response.meta?.nextPage;
      this.totalCount = response.meta?.totalCount || 0;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch companies';
      console.error('Error fetching companies:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Search companies by query
   */
  async searchCompanies(params: CompanySearchParams): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;
      this.searchQuery = params.q;

      const response = await companiesApi.searchCompanies(
        this.currentAccountId,
        params
      );

      this.allCompanies = response.data || [];
      this.currentPage = response.meta?.currentPage || 1;
      this.hasMorePages = !!response.meta?.nextPage;
      this.totalCount = response.meta?.totalCount || 0;
    } catch (err: any) {
      this.error = err.message || 'Failed to search companies';
      console.error('Error searching companies:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch a single company
   */
  async fetchCompany(companyId: number): Promise<Company | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;

      const company = await companiesApi.getCompany(
        this.currentAccountId,
        companyId
      );

      // Update in the store if it exists
      const index = this.allCompanies.findIndex((c) => c.id === company.id);
      if (index !== -1) {
        this.allCompanies[index] = company;
      } else {
        this.allCompanies.push(company);
      }

      return company;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch company';
      console.error('Error fetching company:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Create a new company
   */
  async createCompany(data: CreateCompanyParams): Promise<Company | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isCreating = true;
      this.error = null;

      const newCompany = await companiesApi.createCompany(
        this.currentAccountId,
        data
      );

      this.allCompanies.push(newCompany);
      return newCompany;
    } catch (err: any) {
      this.error = err.message || 'Failed to create company';
      console.error('Error creating company:', err);
      throw err;
    } finally {
      this.isCreating = false;
    }
  }

  /**
   * Update an existing company
   */
  async updateCompany(
    companyId: number,
    data: UpdateCompanyParams
  ): Promise<Company | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isUpdating = true;
      this.error = null;

      const updatedCompany = await companiesApi.updateCompany(
        this.currentAccountId,
        companyId,
        data
      );

      const index = this.allCompanies.findIndex((c) => c.id === companyId);
      if (index !== -1) {
        this.allCompanies[index] = updatedCompany;
      }

      return updatedCompany;
    } catch (err: any) {
      this.error = err.message || 'Failed to update company';
      console.error('Error updating company:', err);
      throw err;
    } finally {
      this.isUpdating = false;
    }
  }

  /**
   * Delete a company
   */
  async deleteCompany(companyId: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      this.isDeleting = true;
      this.error = null;

      await companiesApi.deleteCompany(this.currentAccountId, companyId);

      this.allCompanies = this.allCompanies.filter((c) => c.id !== companyId);
      if (this.selectedCompanyId === companyId) {
        this.selectedCompanyId = null;
      }

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete company';
      console.error('Error deleting company:', err);
      return false;
    } finally {
      this.isDeleting = false;
    }
  }

  /**
   * Select a company
   */
  selectCompany(companyId: number | null): void {
    this.selectedCompanyId = companyId;
  }

  /**
   * Clear search query
   */
  clearSearch(): void {
    this.searchQuery = '';
  }

  /**
   * Clear all companies
   */
  clear(): void {
    this.allCompanies = [];
    this.selectedCompanyId = null;
    this.error = null;
    this.searchQuery = '';
    this.currentPage = 1;
    this.hasMorePages = true;
    this.totalCount = 0;
  }
}

// Export singleton instance
export const companiesStore = new CompaniesStore();
