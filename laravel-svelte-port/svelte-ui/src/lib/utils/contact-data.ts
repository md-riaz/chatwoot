/**
 * Contact Data Transformation Utilities
 * 
 * Handles transformation between Rails API format and frontend display format.
 * Rails stores city, country, company in additional_attributes, but frontend
 * components expect them as direct properties for easier access.
 */

import type { Contact } from '$lib/api/contacts';

/**
 * Transform raw API contact data to frontend-friendly format
 * Adds computed properties for easy access to additional_attributes data
 */
export function transformContactFromApi(rawContact: any): Contact {
  // Create base contact object
  const contact = {
    ...rawContact,
    // Ensure id and timestamps are numbers (API may return strings)
    id: rawContact.id !== undefined ? Number(rawContact.id) : undefined,
    lastActivityAt: rawContact.lastActivityAt ? Number(rawContact.lastActivityAt) : null,
    createdAt: Number(rawContact.createdAt),
    updatedAt: rawContact.updatedAt ? Number(rawContact.updatedAt) : undefined,
  } as Contact;

  // Add computed properties for easy access
  Object.defineProperties(contact, {
    city: {
      get() {
        return this.additionalAttributes?.city || null;
      },
      enumerable: true,
      configurable: true,
    },
    country: {
      get() {
        return this.additionalAttributes?.country || null;
      },
      enumerable: true,
      configurable: true,
    },
    countryCode: {
      get() {
        return this.additionalAttributes?.country_code || null;
      },
      enumerable: true,
      configurable: true,
    },
    company: {
      get() {
        return this.additionalAttributes?.company_name || null;
      },
      enumerable: true,
      configurable: true,
    },
    avatarUrl: {
      get() {
        return this.thumbnail || null;
      },
      enumerable: true,
      configurable: true,
    },
  });

  return contact;
}

/**
 * Transform frontend contact data to Rails API format
 * Moves city, country, company data into additional_attributes
 */
export function transformContactForApi(contactData: any): any {
  const { city, country, countryCode, company, ...baseData } = contactData;

  // Build additional_attributes with Rails-compatible structure
  const additionalAttributes = {
    ...baseData.additionalAttributes,
  };

  // Add location data to additional_attributes if provided
  if (city) additionalAttributes.city = city;
  if (country) additionalAttributes.country = country;
  if (countryCode) additionalAttributes.country_code = countryCode;
  if (company) additionalAttributes.company_name = company;

  return {
    ...baseData,
    additionalAttributes,
  };
}

/**
 * Extract form data from contact for editing
 * Converts Rails format back to form-friendly structure
 */
export function extractContactFormData(contact: Contact | null) {
  if (!contact) {
    return {
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      company: '',
      city: '',
      countryCode: 'US',
      description: '',
      blocked: false,
      socialProfiles: {
        facebook: '',
        twitter: '',
        linkedin: '',
        github: '',
        instagram: ''
      }
    };
  }

  return {
    firstName: contact.name ? contact.name.split(' ')[0] : '',
    lastName: contact.name ? contact.name.split(' ').slice(1).join(' ') : '',
    email: contact.email || '',
    phone: contact.phoneNumber || '',
    company: contact.company || '',
    city: contact.city || '',
    countryCode: contact.countryCode || 'US',
    description: contact.additionalAttributes?.description || '',
    blocked: contact.blocked || false,
    socialProfiles: {
      facebook: '',
      twitter: '',
      linkedin: '',
      github: '',
      instagram: '',
      ...(contact.additionalAttributes?.social_profiles || {})
    }
  };
}

/**
 * Transform form data to API payload
 * Ensures Rails-compatible structure with data in additional_attributes
 */
export function transformFormDataToApi(formData: any): any {
  const {
    firstName,
    lastName,
    email,
    phone,
    company,
    city,
    countryCode,
    description,
    blocked,
    socialProfiles,
    ...rest
  } = formData;

  // Filter out empty social profiles
  const activeSocialProfiles = Object.entries(socialProfiles || {})
    .filter(([_, value]) => value && String(value).trim() !== '')
    .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {});

  return {
    name: `${firstName || ''} ${lastName || ''}`.trim(),
    email: email || null,
    phoneNumber: phone || null,
    blocked: Boolean(blocked),
    additionalAttributes: {
      description: description || '',
      company_name: company || '',
      city: city || '',
      country_code: countryCode || '',
      social_profiles: activeSocialProfiles
    },
    customAttributes: {},
    ...rest
  };
}