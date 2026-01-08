/**
 * Data transformation utilities for API requests/responses
 * Converts between camelCase (frontend) and snake_case (backend)
 */

type AnyObject = Record<string, any>;

/**
 * Convert string from camelCase to snake_case
 */
export function camelToSnake(str: string): string {
  return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
}

/**
 * Convert string from snake_case to camelCase
 */
export function snakeToCamel(str: string): string {
  return str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
}

/**
 * Transform object keys recursively
 * @param obj - Object to transform
 * @param transformer - Function to transform keys
 */
function transformKeys(obj: any, transformer: (key: string) => string): any {
  if (obj === null || obj === undefined) {
    return obj;
  }

  if (Array.isArray(obj)) {
    return obj.map(item => transformKeys(item, transformer));
  }

  if (typeof obj === 'object' && obj.constructor === Object) {
    return Object.keys(obj).reduce((result, key) => {
      const transformedKey = transformer(key);
      result[transformedKey] = transformKeys(obj[key], transformer);
      return result;
    }, {} as AnyObject);
  }

  return obj;
}

/**
 * Convert object keys from camelCase to snake_case recursively
 */
export function keysToSnake<T = any>(obj: any): T {
  return transformKeys(obj, camelToSnake);
}

/**
 * Convert object keys from snake_case to camelCase recursively
 */
export function keysToCamel<T = any>(obj: any): T {
  return transformKeys(obj, snakeToCamel);
}

/**
 * Generic key transformer
 * @param obj - Object to transform
 * @param format - Target format ('snake' or 'camel')
 */
export function transformKeysTo<T = any>(obj: any, format: 'snake' | 'camel'): T {
  return format === 'snake' ? keysToSnake(obj) : keysToCamel(obj);
}
