/**
 * Date and number formatting utilities
 * Built on top of date-fns and Intl APIs
 */

import { format, formatDistanceToNow, isToday, isYesterday, parseISO } from 'date-fns';
import { enUS, es, fr, de, pt, ar, ja, ko, zhCN, ru } from 'date-fns/locale';

/**
 * Date-fns locale map
 */
const dateFnsLocales: Record<string, Locale> = {
  'en': enUS,
  'es': es,
  'fr': fr,
  'de': de,
  'pt': pt,
  'pt_BR': pt,
  'ar': ar,
  'ja': ja,
  'ko': ko,
  'zh_CN': zhCN,
  'zh_TW': zhCN,
  'ru': ru
};

/**
 * Get date-fns locale for current language
 */
function getDateFnsLocale(locale: string): Locale {
  return dateFnsLocales[locale] || enUS;
}

/**
 * Format date with locale
 */
export function formatDate(
  date: Date | string | number,
  formatStr: string = 'PPpp',
  locale: string = 'en'
): string {
  const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
  return format(dateObj, formatStr, { locale: getDateFnsLocale(locale) });
}

/**
 * Format relative time (e.g., "2 hours ago")
 */
export function formatRelativeTime(
  date: Date | string | number,
  locale: string = 'en'
): string {
  const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
  return formatDistanceToNow(dateObj, {
    addSuffix: true,
    locale: getDateFnsLocale(locale)
  });
}

/**
 * Format date for display (smart formatting based on recency)
 */
export function formatSmartDate(
  date: Date | string | number,
  locale: string = 'en'
): string {
  const dateObj = typeof date === 'string' ? parseISO(date) : new Date(date);
  
  if (isToday(dateObj)) {
    return format(dateObj, 'p', { locale: getDateFnsLocale(locale) }); // Time only
  }
  
  if (isYesterday(dateObj)) {
    return 'Yesterday';
  }
  
  // This year - show month and day
  if (dateObj.getFullYear() === new Date().getFullYear()) {
    return format(dateObj, 'MMM d', { locale: getDateFnsLocale(locale) });
  }
  
  // Different year - show full date
  return format(dateObj, 'MMM d, yyyy', { locale: getDateFnsLocale(locale) });
}

/**
 * Format number with locale
 */
export function formatNumber(
  value: number,
  options: Intl.NumberFormatOptions = {},
  locale: string = 'en-US'
): string {
  return new Intl.NumberFormat(locale, options).format(value);
}

/**
 * Format currency
 */
export function formatCurrency(
  value: number,
  currency: string = 'USD',
  locale: string = 'en-US'
): string {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency
  }).format(value);
}

/**
 * Format percentage
 */
export function formatPercentage(
  value: number,
  decimals: number = 0,
  locale: string = 'en-US'
): string {
  return new Intl.NumberFormat(locale, {
    style: 'percent',
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value / 100);
}

/**
 * Format file size
 */
export function formatFileSize(bytes: number, locale: string = 'en-US'): string {
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  let size = bytes;
  let unitIndex = 0;
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex++;
  }
  
  return `${formatNumber(size, { maximumFractionDigits: 2 }, locale)} ${units[unitIndex]}`;
}

/**
 * Format duration (seconds to human readable)
 */
export function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }
  
  if (minutes > 0) {
    return `${minutes}m ${secs}s`;
  }
  
  return `${secs}s`;
}

/**
 * Format compact number (1.2K, 3.4M, etc.)
 */
export function formatCompactNumber(value: number, locale: string = 'en-US'): string {
  return new Intl.NumberFormat(locale, {
    notation: 'compact',
    compactDisplay: 'short'
  }).format(value);
}

/**
 * Format phone number (basic formatting)
 */
export function formatPhoneNumber(phone: string): string {
  // Remove all non-digit characters
  const cleaned = phone.replace(/\D/g, '');
  
  // US format
  if (cleaned.length === 10) {
    return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
  }
  
  // International format
  if (cleaned.length === 11 && cleaned.startsWith('1')) {
    return `+1 (${cleaned.slice(1, 4)}) ${cleaned.slice(4, 7)}-${cleaned.slice(7)}`;
  }
  
  // Default: return original
  return phone;
}
