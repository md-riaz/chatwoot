/**
 * Contact API
 * 
 * API methods for managing widget contact information.
 */

import { getWidgetApi } from './client';
import type { Contact, ApiResponse } from './types';

/**
 * Update contact information
 */
export async function updateContact(contact: Partial<Contact>): Promise<Contact> {
  const api = getWidgetApi();
  const response = await api
    .patch('contact', { json: contact })
    .json<ApiResponse<Contact>>();
  return response.data;
}

/**
 * Set custom attributes for contact
 */
export async function setCustomAttributes(
  attributes: Record<string, any>
): Promise<Contact> {
  const api = getWidgetApi();
  const response = await api
    .post('contact/custom_attributes', { json: { customAttributes: attributes } })
    .json<ApiResponse<Contact>>();
  return response.data;
}
