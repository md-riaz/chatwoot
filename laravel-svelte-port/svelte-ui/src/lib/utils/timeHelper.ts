/**
 * Time Helper Utilities
 * Functions for formatting and manipulating time values
 */

/**
 * Formats a time duration in seconds to a human-readable string
 * @param seconds - Duration in seconds
 * @param format - Format type ('short' | 'long')
 * @returns Formatted time string (e.g., "2h 30m" or "2 hours 30 minutes")
 */
export function formatTime(seconds: number | null | undefined, format: 'short' | 'long' = 'short'): string {
  if (seconds === null || seconds === undefined || isNaN(seconds)) {
    return format === 'short' ? '0s' : '0 seconds';
  }

  const absSeconds = Math.abs(seconds);
  
  const days = Math.floor(absSeconds / 86400);
  const hours = Math.floor((absSeconds % 86400) / 3600);
  const minutes = Math.floor((absSeconds % 3600) / 60);
  const secs = Math.floor(absSeconds % 60);

  const parts: string[] = [];

  if (format === 'short') {
    if (days > 0) parts.push(`${days}d`);
    if (hours > 0) parts.push(`${hours}h`);
    if (minutes > 0) parts.push(`${minutes}m`);
    if (secs > 0 || parts.length === 0) parts.push(`${secs}s`);
  } else {
    if (days > 0) parts.push(`${days} ${days === 1 ? 'day' : 'days'}`);
    if (hours > 0) parts.push(`${hours} ${hours === 1 ? 'hour' : 'hours'}`);
    if (minutes > 0) parts.push(`${minutes} ${minutes === 1 ? 'minute' : 'minutes'}`);
    if (secs > 0 || parts.length === 0) parts.push(`${secs} ${secs === 1 ? 'second' : 'seconds'}`);
  }

  return parts.join(' ');
}

/**
 * Formats a timestamp to a date string
 * @param timestamp - Unix timestamp (seconds or milliseconds)
 * @param format - Date format ('short' | 'long' | 'time' | 'datetime')
 * @returns Formatted date string
 */
export function formatDate(
  timestamp: number | string | Date,
  format: 'short' | 'long' | 'time' | 'datetime' = 'short'
): string {
  let date: Date;

  if (timestamp instanceof Date) {
    date = timestamp;
  } else if (typeof timestamp === 'string') {
    date = new Date(timestamp);
  } else {
    // Handle both seconds and milliseconds timestamps
    date = timestamp > 10000000000 ? new Date(timestamp) : new Date(timestamp * 1000);
  }

  if (isNaN(date.getTime())) {
    return 'Invalid date';
  }

  const options: Intl.DateTimeFormatOptions = {};

  switch (format) {
    case 'short':
      options.year = 'numeric';
      options.month = 'short';
      options.day = 'numeric';
      break;
    case 'long':
      options.year = 'numeric';
      options.month = 'long';
      options.day = 'numeric';
      break;
    case 'time':
      options.hour = '2-digit';
      options.minute = '2-digit';
      break;
    case 'datetime':
      options.year = 'numeric';
      options.month = 'short';
      options.day = 'numeric';
      options.hour = '2-digit';
      options.minute = '2-digit';
      break;
  }

  return date.toLocaleString('en-US', options);
}

/**
 * Gets the start and end timestamps for a given date range
 * @param days - Number of days to go back
 * @returns Object with from and to timestamps (in seconds)
 */
export function getDateRange(days: number): { from: number; to: number } {
  const to = Math.floor(Date.now() / 1000);
  const from = to - days * 24 * 60 * 60;
  return { from, to };
}

/**
 * Converts a Date object to Unix timestamp (seconds)
 * @param date - Date object
 * @returns Unix timestamp in seconds
 */
export function dateToTimestamp(date: Date): number {
  return Math.floor(date.getTime() / 1000);
}

/**
 * Converts Unix timestamp (seconds) to Date object
 * @param timestamp - Unix timestamp in seconds
 * @returns Date object
 */
export function timestampToDate(timestamp: number): Date {
  return new Date(timestamp * 1000);
}

/**
 * Checks if a timestamp is today
 * @param timestamp - Unix timestamp (seconds)
 * @returns True if timestamp is today
 */
export function isToday(timestamp: number): boolean {
  const date = timestampToDate(timestamp);
  const today = new Date();
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  );
}

/**
 * Gets relative time string (e.g., "2 hours ago", "just now")
 * @param timestamp - Unix timestamp (seconds)
 * @returns Relative time string
 */
export function getRelativeTime(timestamp: number): string {
  const now = Math.floor(Date.now() / 1000);
  const diff = now - timestamp;

  if (diff < 60) return 'just now';
  if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
  if (diff < 2592000) return `${Math.floor(diff / 604800)} weeks ago`;
  if (diff < 31536000) return `${Math.floor(diff / 2592000)} months ago`;
  return `${Math.floor(diff / 31536000)} years ago`;
}
