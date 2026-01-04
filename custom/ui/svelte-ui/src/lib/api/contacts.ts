import { api } from './client';
import type { PaginatedResponse } from './types';

// Contact interfaces
export interface Contact {
  id: number;
  accountId: number;
  name: string;
  email: string | null;
  phoneNumber: string | null;
  identifier: string | null;
  avatarUrl: string | null;
  customAttributes: Record<string, any>;
  additionalAttributes: Record<string, any>;
  lastActivityAt: string | null;
  createdAt: string;
  updatedAt?: string;
  conversationsCount?: number;
  // Legacy support
  thumbnail?: string | null;
  company?: string | null;
  city?: string | null;
  country?: string | null;
  countryCode?: string | null;
  availabilityStatus?: string | null;
  conversations?: any[];
  socialProfiles?: SocialProfile[];
}

export interface SocialProfile {
  id: number;
  type: string;
  url: string;
}

export interface ContactListParams {
  page?: number;
  perPage?: number;
  sort?: string;
  q?: string;
  [key: string]: string | number | boolean | undefined;
}

export interface CreateContactParams {
  name?: string;
  email?: string;
  phoneNumber?: string;
  identifier?: string;
  customAttributes?: Record<string, any>;
  additionalAttributes?: Record<string, any>;
  socialProfiles?: Partial<SocialProfile>[];
}

export interface UpdateContactParams extends CreateContactParams {
  avatar?: File;
  avatarUrl?: string;
}

export interface ContactFilterParams {
  q?: string;
  labels?: string[];
  inboxId?: number;
  page?: number;
  perPage?: number;
}

export interface ImportContactsParams {
  file: File;
}

export interface ContactConversation {
  id: number;
  status: string;
  inboxId: number;
  // ... other conversation fields
}

/**
 * Get paginated list of contacts
 */
export async function getContacts(
  accountId: number,
  params: ContactListParams = {}
): Promise<PaginatedResponse<Contact>> {
  return api
    .get(`api/v1/accounts/${accountId}/contacts`, {
      searchParams: params,
    })
    .json();
}

/**
 * Search contacts with query
 */
export async function searchContacts(
  accountId: number,
  query: string,
  page = 1,
  perPage = 15
): Promise<PaginatedResponse<Contact>> {
  return api
    .get(`api/v1/accounts/${accountId}/contacts/search`, {
      searchParams: { q: query, page, per_page: perPage },
    })
    .json();
}

/**
 * Filter contacts with advanced filters
 */
export async function filterContacts(
  accountId: number,
  params: ContactFilterParams
): Promise<PaginatedResponse<Contact>> {
  return api
    .post(`api/v1/accounts/${accountId}/contacts/filter`, {
      json: params,
    })
    .json();
}

/**
 * Get single contact by ID
 */
export async function getContact(
  accountId: number,
  contactId: number
): Promise<Contact> {
  return api.get(`api/v1/accounts/${accountId}/contacts/${contactId}`).json();
}

/**
 * Create new contact
 */
export async function createContact(
  accountId: number,
  params: CreateContactParams
): Promise<Contact> {
  return api
    .post(`api/v1/accounts/${accountId}/contacts`, {
      json: params,
    })
    .json();
}

/**
 * Update existing contact
 */
export async function updateContact(
  accountId: number,
  contactId: number,
  params: UpdateContactParams
): Promise<Contact> {
  // If avatar file is provided, use FormData
  if (params.avatar) {
    const formData = new FormData();
    formData.append('avatar', params.avatar);

    // Add other fields
    Object.entries(params).forEach(([key, value]) => {
      if (key !== 'avatar' && value !== undefined) {
        if (typeof value === 'object') {
          formData.append(key, JSON.stringify(value));
        } else {
          formData.append(key, String(value));
        }
      }
    });

    return api
      .patch(`api/v1/accounts/${accountId}/contacts/${contactId}`, {
        body: formData,
      })
      .json();
  }

  // Otherwise use JSON
  return api
    .patch(`api/v1/accounts/${accountId}/contacts/${contactId}`, {
      json: params,
    })
    .json();
}

/**
 * Delete contact
 */
export async function deleteContact(
  accountId: number,
  contactId: number
): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/contacts/${contactId}`);
}

/**
 * Delete contact avatar
 */
export async function deleteContactAvatar(
  accountId: number,
  contactId: number
): Promise<Contact> {
  return api
    .delete(`api/v1/accounts/${accountId}/contacts/${contactId}/avatar`)
    .json();
}

/**
 * Get contact conversations
 */
export async function getContactConversations(
  accountId: number,
  contactId: number
): Promise<ContactConversation[]> {
  return api
    .get(`api/v1/accounts/${accountId}/contacts/${contactId}/conversations`)
    .json();
}

/**
 * Merge contacts (primary absorbs secondary)
 */
export async function mergeContacts(
  accountId: number,
  primaryContactId: number,
  secondaryContactId: number
): Promise<Contact> {
  return api
    .post(`api/v1/accounts/${accountId}/contacts/${primaryContactId}/merge`, {
      json: { child_contact_id: secondaryContactId },
    })
    .json();
}

/**
 * Import contacts from file
 */
export async function importContacts(
  accountId: number,
  file: File
): Promise<{ success: boolean; failed: number; total: number }> {
  const formData = new FormData();
  formData.append('import_file', file);

  return api
    .post(`api/v1/accounts/${accountId}/contacts/import`, {
      body: formData,
    })
    .json();
}

/**
 * Export contacts
 */
export async function exportContacts(accountId: number): Promise<Blob> {
  return api
    .get(`api/v1/accounts/${accountId}/contacts/export`)
    .blob();
}
