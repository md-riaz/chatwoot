/**
 * LocalStorage persistence utilities for Svelte stores
 */

const STORAGE_PREFIX = 'chatwoot_';

/**
 * Save data to localStorage
 */
export function saveToStorage<T>(key: string, data: T): void {
  if (typeof localStorage === 'undefined') return;
  
  try {
    const serialized = JSON.stringify(data);
    localStorage.setItem(`${STORAGE_PREFIX}${key}`, serialized);
  } catch (error) {
    console.error(`Failed to save to localStorage (key: ${key}):`, error);
  }
}

/**
 * Load data from localStorage
 */
export function loadFromStorage<T>(key: string): T | null {
  if (typeof localStorage === 'undefined') return null;
  
  try {
    const item = localStorage.getItem(`${STORAGE_PREFIX}${key}`);
    if (!item) return null;
    
    return JSON.parse(item) as T;
  } catch (error) {
    console.error(`Failed to load from localStorage (key: ${key}):`, error);
    return null;
  }
}

/**
 * Remove data from localStorage
 */
export function clearStorage(key: string): void {
  if (typeof localStorage === 'undefined') return;
  
  try {
    localStorage.removeItem(`${STORAGE_PREFIX}${key}`);
  } catch (error) {
    console.error(`Failed to clear localStorage (key: ${key}):`, error);
  }
}

/**
 * Clear all Chatwoot data from localStorage
 */
export function clearAllStorage(): void {
  if (typeof localStorage === 'undefined') return;
  
  try {
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
      if (key.startsWith(STORAGE_PREFIX)) {
        localStorage.removeItem(key);
      }
    });
  } catch (error) {
    console.error('Failed to clear all storage:', error);
  }
}

/**
 * Check if localStorage is available
 */
export function isStorageAvailable(): boolean {
  if (typeof localStorage === 'undefined') return false;
  
  try {
    const testKey = `${STORAGE_PREFIX}__test__`;
    localStorage.setItem(testKey, 'test');
    localStorage.removeItem(testKey);
    return true;
  } catch {
    return false;
  }
}

/**
 * Get storage size in bytes (approximate)
 */
export function getStorageSize(): number {
  if (typeof localStorage === 'undefined') return 0;
  
  try {
    let size = 0;
    const keys = Object.keys(localStorage);
    
    keys.forEach(key => {
      if (key.startsWith(STORAGE_PREFIX)) {
        const value = localStorage.getItem(key);
        if (value) {
          size += key.length + value.length;
        }
      }
    });
    
    return size;
  } catch {
    return 0;
  }
}
