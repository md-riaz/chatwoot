/**
 * Events API
 * 
 * API methods for tracking widget events.
 */

import { getWidgetApi } from './client';
import type { EventData } from './types';

/**
 * Track a custom event
 */
export async function trackEvent(event: EventData): Promise<void> {
  const api = getWidgetApi();
  await api.post('events', { json: event }).json();
}

/**
 * Track page view
 */
export async function trackPageView(url: string): Promise<void> {
  const api = getWidgetApi();
  await api.post('events/page_view', { json: { url } }).json();
}
