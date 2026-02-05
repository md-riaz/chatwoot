import type { WidgetCampaign } from '$lib/api/widget-campaigns';

/**
 * Campaign helper utilities ported from Vue implementation
 */

/**
 * Check if URL pattern matches the current URL
 */
export function isPatternMatchingWithURL(urlPattern: string, url: string): boolean {
  try {
    let updatedUrlPattern = urlPattern;
    const locationObj = new URL(url);

    // Handle trailing slash in pattern
    if (updatedUrlPattern.endsWith('/')) {
      updatedUrlPattern = updatedUrlPattern.slice(0, -1) + '*\\?*\\#*';
    }

    // Handle trailing slash in URL
    if (locationObj.pathname.endsWith('/')) {
      locationObj.pathname = locationObj.pathname.slice(0, -1);
    }

    // Use URLPattern for matching (with polyfill support)
    if (typeof URLPattern !== 'undefined') {
      const pattern = new URLPattern(updatedUrlPattern);
      return pattern.test(locationObj.toString());
    } else {
      // Fallback for browsers without URLPattern support
      return simplePatternMatch(updatedUrlPattern, locationObj.toString());
    }
  } catch (error) {
    console.warn('URL pattern matching failed:', error);
    return false;
  }
}

/**
 * Simple pattern matching fallback for browsers without URLPattern
 */
function simplePatternMatch(pattern: string, url: string): boolean {
  // Convert pattern to regex
  const regexPattern = pattern
    .replace(/\*/g, '.*')
    .replace(/\?/g, '\\?')
    .replace(/#/g, '\\#');
  
  try {
    const regex = new RegExp(`^${regexPattern}$`, 'i');
    return regex.test(url);
  } catch (error) {
    console.warn('Regex pattern matching failed:', error);
    return false;
  }
}

/**
 * Format campaigns for timer processing
 */
export interface FormattedCampaign {
  id: number;
  triggerOnlyDuringBusinessHours: boolean;
  timeOnPage?: number;
  url?: string;
}

export function formatCampaigns(params: { campaigns: WidgetCampaign[] }): FormattedCampaign[] {
  const { campaigns } = params;
  
  return campaigns.map(campaign => ({
    id: campaign.id,
    triggerOnlyDuringBusinessHours: campaign.trigger_only_during_business_hours || false,
    timeOnPage: campaign.trigger_rules?.timeOnPage,
    url: campaign.trigger_rules?.url,
  }));
}

/**
 * Filter campaigns based on current URL and business hours
 */
export function filterCampaigns(params: {
  campaigns: WidgetCampaign[];
  currentURL: string;
  isInBusinessHours: boolean;
}): FormattedCampaign[] {
  const { campaigns, currentURL, isInBusinessHours } = params;
  
  const formattedCampaigns = formatCampaigns({ campaigns });
  
  return formattedCampaigns.filter(campaign => {
    // Check URL pattern matching
    if (campaign.url && !isPatternMatchingWithURL(campaign.url, currentURL)) {
      return false;
    }
    
    // Check business hours requirement
    if (campaign.triggerOnlyDuringBusinessHours) {
      return isInBusinessHours;
    }
    
    return true;
  });
}