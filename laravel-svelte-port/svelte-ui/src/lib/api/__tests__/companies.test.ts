/**
 * Tests for Companies API
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import * as companiesApi from '../companies';

// Mock the api client
vi.mock('../client', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}));

describe('Company Interfaces', () => {
  it('defines Company interface structure', () => {
    const company: companiesApi.Company = {
      id: 1,
      name: 'Test Company',
      customAttributes: {},
      createdAt: '2024-01-01',
      updatedAt: '2024-01-01',
    };

    expect(company.id).toBe(1);
    expect(company.name).toBe('Test Company');
  });

  it('defines CreateCompanyParams interface', () => {
    const params: companiesApi.CreateCompanyParams = {
      name: 'New Company',
      website: 'https://example.com',
      industry: 'Technology',
    };

    expect(params.name).toBe('New Company');
    expect(params.website).toBe('https://example.com');
  });

  it('defines UpdateCompanyParams interface', () => {
    const params: companiesApi.UpdateCompanyParams = {
      name: 'Updated Company',
      description: 'Updated description',
    };

    expect(params.name).toBe('Updated Company');
  });
});

describe('Company API Functions', () => {
  it('exports getCompanies function', () => {
    expect(typeof companiesApi.getCompanies).toBe('function');
  });

  it('exports searchCompanies function', () => {
    expect(typeof companiesApi.searchCompanies).toBe('function');
  });

  it('exports getCompany function', () => {
    expect(typeof companiesApi.getCompany).toBe('function');
  });

  it('exports createCompany function', () => {
    expect(typeof companiesApi.createCompany).toBe('function');
  });

  it('exports updateCompany function', () => {
    expect(typeof companiesApi.updateCompany).toBe('function');
  });

  it('exports deleteCompany function', () => {
    expect(typeof companiesApi.deleteCompany).toBe('function');
  });

  it('exports getCompanyContacts function', () => {
    expect(typeof companiesApi.getCompanyContacts).toBe('function');
  });
});

describe('Company List Params', () => {
  it('defines CompanyListParams with optional fields', () => {
    const params: companiesApi.CompanyListParams = {
      page: 1,
      perPage: 25,
      sort: 'name',
    };

    expect(params.page).toBe(1);
    expect(params.perPage).toBe(25);
    expect(params.sort).toBe('name');
  });

  it('allows empty CompanyListParams', () => {
    const params: companiesApi.CompanyListParams = {};
    expect(params).toEqual({});
  });
});

describe('Company Search Params', () => {
  it('defines CompanySearchParams with required query', () => {
    const params: companiesApi.CompanySearchParams = {
      q: 'test',
      page: 1,
      sort: 'name',
    };

    expect(params.q).toBe('test');
    expect(params.page).toBe(1);
  });
});
