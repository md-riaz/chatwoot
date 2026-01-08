/**
 * Validation Utilities
 * Common validation functions for forms and data
 */

/**
 * Check if value is empty (null, undefined, empty string, empty array, empty object)
 */
export function isEmpty(value: any): boolean {
  if (!value) return true;

  if (Array.isArray(value)) return value.length === 0;

  if (typeof value === 'object') return Object.keys(value).length === 0;

  return false;
}

/**
 * Validate email address
 */
export function isValidEmail(email: string): boolean {
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return EMAIL_REGEX.test(email);
}

/**
 * Validate phone number (international format)
 */
export function isValidPhone(phone: string): boolean {
  // Basic validation - can be enhanced based on requirements
  const PHONE_REGEX = /^[\d\s()+-]{7,20}$/;
  return PHONE_REGEX.test(phone);
}

/**
 * Validate URL
 */
export function isValidURL(url: string): boolean {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

/**
 * Validate if string is valid JSON
 */
export function isValidJSON(value: string): boolean {
  try {
    JSON.parse(value);
    return true;
  } catch {
    return false;
  }
}

/**
 * Validate required field
 */
export function isRequired(value: any): boolean {
  return !isEmpty(value);
}

/**
 * Validate minimum length
 */
export function minLength(value: string, min: number): boolean {
  return value.length >= min;
}

/**
 * Validate maximum length
 */
export function maxLength(value: string, max: number): boolean {
  return value.length <= max;
}

/**
 * Validate number range
 */
export function inRange(value: number, min: number, max: number): boolean {
  return value >= min && value <= max;
}

/**
 * Validate password strength
 * At least 8 characters, one uppercase, one lowercase, one number
 */
export function isStrongPassword(password: string): boolean {
  const hasMinLength = password.length >= 8;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumber = /\d/.test(password);

  return hasMinLength && hasUpperCase && hasLowerCase && hasNumber;
}

/**
 * Validate hex color code
 */
export function isValidHexColor(color: string): boolean {
  const HEX_COLOR_REGEX = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
  return HEX_COLOR_REGEX.test(color);
}

/**
 * Validate slug (URL-friendly string)
 */
export function isValidSlug(slug: string): boolean {
  const SLUG_REGEX = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
  return SLUG_REGEX.test(slug);
}

/**
 * Validate IP address (IPv4)
 */
export function isValidIPv4(ip: string): boolean {
  const IPV4_REGEX = /^(\d{1,3}\.){3}\d{1,3}$/;
  if (!IPV4_REGEX.test(ip)) return false;

  const parts = ip.split('.');
  return parts.every(part => {
    const num = parseInt(part, 10);
    return num >= 0 && num <= 255;
  });
}

/**
 * Validate credit card number (Luhn algorithm)
 */
export function isValidCreditCard(cardNumber: string): boolean {
  const cleaned = cardNumber.replace(/\s/g, '');
  if (!/^\d+$/.test(cleaned)) return false;

  let sum = 0;
  let isEven = false;

  for (let i = cleaned.length - 1; i >= 0; i--) {
    let digit = parseInt(cleaned[i], 10);

    if (isEven) {
      digit *= 2;
      if (digit > 9) digit -= 9;
    }

    sum += digit;
    isEven = !isEven;
  }

  return sum % 10 === 0;
}

/**
 * Validate that value matches a pattern
 */
export function matchesPattern(value: string, pattern: RegExp): boolean {
  return pattern.test(value);
}

/**
 * Validate that two values match (e.g., password confirmation)
 */
export function valuesMatch(value1: any, value2: any): boolean {
  return value1 === value2;
}

/**
 * Validation error messages
 */
export const VALIDATION_MESSAGES = {
  REQUIRED: 'This field is required',
  INVALID_EMAIL: 'Please enter a valid email address',
  INVALID_PHONE: 'Please enter a valid phone number',
  INVALID_URL: 'Please enter a valid URL',
  INVALID_JSON: 'Invalid JSON format',
  MIN_LENGTH: (min: number) => `Minimum ${min} characters required`,
  MAX_LENGTH: (max: number) => `Maximum ${max} characters allowed`,
  OUT_OF_RANGE: (min: number, max: number) => `Value must be between ${min} and ${max}`,
  WEAK_PASSWORD: 'Password must be at least 8 characters with uppercase, lowercase, and number',
  VALUES_DONT_MATCH: 'Values do not match',
} as const;

/**
 * Validate filter (for automations/custom views)
 */
export function validateFilter(filter: {
  attribute_key?: string;
  filter_operator?: string;
  values?: any;
}): string | null {
  if (!filter.attribute_key) {
    return 'Attribute key is required';
  }

  if (!filter.filter_operator) {
    return 'Filter operator is required';
  }

  const operatorRequiresValue = !['is_present', 'is_not_present'].includes(
    filter.filter_operator
  );

  if (operatorRequiresValue && isEmpty(filter.values)) {
    return 'Value is required';
  }

  if (
    filter.filter_operator === 'days_before' &&
    (parseInt(filter.values, 10) <= 0 || parseInt(filter.values, 10) >= 999)
  ) {
    return 'Value must be between 1 and 998';
  }

  return null;
}
