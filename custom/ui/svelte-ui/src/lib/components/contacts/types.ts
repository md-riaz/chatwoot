/**
 * Contact component types
 */

export interface ContactAttribute {
  id: number;
  key: string;
  value: string;
  type: 'text' | 'number' | 'link' | 'date' | 'list';
}

export interface ContactInfo {
  id: number;
  name: string;
  email?: string;
  phone_number?: string;
  avatar_url?: string;
  availability_status?: string;
  custom_attributes?: Record<string, any>;
  social_profiles?: {
    facebook?: string;
    twitter?: string;
    linkedin?: string;
  };
  company_name?: string;
  city?: string;
  country?: string;
}
