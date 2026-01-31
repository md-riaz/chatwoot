import { api } from './client';
import type { PaginatedResponse } from './types';

/**
 * Company interfaces
 */
export interface Company {
  id: number;
  name: string;
  domain?: string;
  description?: string;
  website?: string;
  industry?: string;
  size?: string;
  employees?: number;
  customAttributes: Record<string, any>;
  contactsCount?: number;
  conversationsCount?: number;
  created_at: string;
  updated_at: string;
}

export interface CreateCompanyParams {
  name: string;
  domain?: string;
  description?: string;
  website?: string;
  industry?: string;
  size?: string;
  customAttributes?: Record<string, any>;
}

export interface UpdateCompanyParams extends Partial<CreateCompanyParams> { }

export interface CompanyListParams {
  page?: number;
  perPage?: number;
  sort?: string;
  q?: string;
  [key: string]: string | number | boolean | undefined;
}

export interface CompanySearchParams {
  q: string;
  page?: number;
  perPage?: number;
  sort?: string;
}

/**
 * Get paginated list of companies
 */
export async function getCompanies(
  accountId: number,
  params: CompanyListParams = {}
): Promise<PaginatedResponse<Company>> {
  const searchParams = new URLSearchParams();
  if (params.page) searchParams.set('page', params.page.toString());
  if (params.perPage) searchParams.set('per_page', params.perPage.toString());
  if (params.sort) searchParams.set('sort', params.sort);

  const query = searchParams.toString();
  const url = `api/v1/accounts/${accountId}/companies${query ? `?${query}` : ''}`;

  return api.get(url).json();
}

/**
 * Search companies
 */
export async function searchCompanies(
  accountId: number,
  params: CompanySearchParams
): Promise<PaginatedResponse<Company>> {
  const searchParams = new URLSearchParams();
  searchParams.set('q', params.q);
  if (params.page) searchParams.set('page', params.page.toString());
  if (params.perPage) searchParams.set('per_page', params.perPage.toString());
  if (params.sort) searchParams.set('sort', params.sort);

  const query = searchParams.toString();
  return api
    .get(`api/v1/accounts/${accountId}/companies/search?${query}`)
    .json();
}

/**
 * Get a single company
 */
export async function getCompany(
  accountId: number,
  companyId: number
): Promise<Company> {
  return api
    .get(`api/v1/accounts/${accountId}/companies/${companyId}`)
    .json<{ data: Company }>()
    .then((r: { data: Company }) => r.data);
}

/**
 * Create a new company
 */
export async function createCompany(
  accountId: number,
  data: CreateCompanyParams
): Promise<Company> {
  return api
    .post(`api/v1/accounts/${accountId}/companies`, { json: data })
    .json<{ data: Company }>()
    .then((r: { data: Company }) => r.data);
}

/**
 * Update an existing company
 */
export async function updateCompany(
  accountId: number,
  companyId: number,
  data: UpdateCompanyParams
): Promise<Company> {
  return api
    .patch(`api/v1/accounts/${accountId}/companies/${companyId}`, {
      json: data,
    })
    .json<{ data: Company }>()
    .then((r: { data: Company }) => r.data);
}

/**
 * Delete a company
 */
export async function deleteCompany(
  accountId: number,
  companyId: number
): Promise<void> {
  return api
    .delete(`api/v1/accounts/${accountId}/companies/${companyId}`)
    .json();
}

/**
 * Get contacts for a company
 */
export async function getCompanyContacts(
  accountId: number,
  companyId: number,
  params: { page?: number; perPage?: number } = {}
): Promise<any> {
  const searchParams = new URLSearchParams();
  if (params.page) searchParams.set('page', params.page.toString());
  if (params.perPage) searchParams.set('per_page', params.perPage.toString());

  const query = searchParams.toString();
  const url = `api/v1/accounts/${accountId}/companies/${companyId}/contacts${query ? `?${query}` : ''}`;

  return api.get(url).json();
}
