/**
 * URL Utilities
 * Helper functions for URL construction and manipulation
 */

/**
 * Build frontend URL with optional query parameters
 * @param path - Path within the app
 * @param params - Optional query parameters
 * @returns Frontend URL
 */
export function frontendURL(path: string, params?: Record<string, any>): string {
  const stringifiedParams = params ? `?${new URLSearchParams(params as any)}` : '';
  return `/app/${path}${stringifiedParams}`;
}

/**
 * Build conversation URL based on context
 */
export function conversationURL(options: {
  accountId: number;
  id: number;
  activeInbox?: number;
  label?: string;
  teamId?: number;
  conversationType?: string;
  foldersId?: number;
}): string {
  const { accountId, id, activeInbox, label, teamId, conversationType, foldersId } = options;

  let url = `accounts/${accountId}/conversations/${id}`;

  if (activeInbox) {
    url = `accounts/${accountId}/inbox/${activeInbox}/conversations/${id}`;
  } else if (label) {
    url = `accounts/${accountId}/label/${label}/conversations/${id}`;
  } else if (teamId) {
    url = `accounts/${accountId}/team/${teamId}/conversations/${id}`;
  } else if (foldersId && foldersId !== 0) {
    url = `accounts/${accountId}/custom_view/${foldersId}/conversations/${id}`;
  } else if (conversationType === 'mention') {
    url = `accounts/${accountId}/mentions/conversations/${id}`;
  } else if (conversationType === 'participating') {
    url = `accounts/${accountId}/participating/conversations/${id}`;
  } else if (conversationType === 'unattended') {
    url = `accounts/${accountId}/unattended/conversations/${id}`;
  }

  return url;
}

/**
 * Build conversation list page URL
 */
export function conversationListPageURL(options: {
  accountId: number;
  conversationType?: string;
  inboxId?: number;
  label?: string;
  teamId?: number;
  customViewId?: number;
}): string {
  const { accountId, conversationType, inboxId, label, teamId, customViewId } = options;

  let url = `accounts/${accountId}/dashboard`;

  if (label) {
    url = `accounts/${accountId}/label/${label}`;
  } else if (teamId) {
    url = `accounts/${accountId}/team/${teamId}`;
  } else if (inboxId) {
    url = `accounts/${accountId}/inbox/${inboxId}`;
  } else if (customViewId) {
    url = `accounts/${accountId}/custom_view/${customViewId}`;
  } else if (conversationType) {
    const urlMap: Record<string, string> = {
      mention: 'mentions/conversations',
      unattended: 'unattended/conversations',
    };
    url = `accounts/${accountId}/${urlMap[conversationType]}`;
  }

  return frontendURL(url);
}

/**
 * Build contact URL
 */
export function contactURL(accountId: number, contactId: number): string {
  return `accounts/${accountId}/contacts/${contactId}`;
}

/**
 * Build settings URL
 */
export function settingsURL(accountId: number, section?: string): string {
  const base = `accounts/${accountId}/settings`;
  return section ? `${base}/${section}` : base;
}

/**
 * Build reports URL
 */
export function reportsURL(accountId: number, reportType?: string): string {
  const base = `accounts/${accountId}/reports`;
  return reportType ? `${base}/${reportType}` : base;
}

/**
 * Validate if string is a valid URL
 */
export function isValidURL(value: string): boolean {
  const URL_REGEX =
    /^https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_+.~#?&//=]*)$/gm;
  return URL_REGEX.test(value);
}

/**
 * Parse query string into object
 */
export function parseQueryString(queryString: string): Record<string, string> {
  const params = new URLSearchParams(queryString);
  const result: Record<string, string> = {};
  params.forEach((value, key) => {
    result[key] = value;
  });
  return result;
}

/**
 * Build query string from object
 */
export function buildQueryString(params: Record<string, any>): string {
  const searchParams = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== null && value !== undefined) {
      searchParams.append(key, String(value));
    }
  });
  return searchParams.toString();
}

/**
 * Add query parameters to URL
 */
export function addQueryParams(url: string, params: Record<string, any>): string {
  const [baseUrl, existingQuery] = url.split('?');
  const existingParams = existingQuery ? parseQueryString(existingQuery) : {};
  const mergedParams = { ...existingParams, ...params };
  const queryString = buildQueryString(mergedParams);
  return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}

/**
 * Get domain from URL
 */
export function getDomain(url: string): string {
  try {
    const urlObj = new URL(url);
    return urlObj.hostname;
  } catch {
    return '';
  }
}

/**
 * Check if URL is external
 */
export function isExternalURL(url: string): boolean {
  try {
    const urlObj = new URL(url);
    return urlObj.origin !== window.location.origin;
  } catch {
    return false;
  }
}
