/**
 * Widget Event Constants
 * Ported from Vue widget bus events
 */

// Campaign events
export const ON_CAMPAIGN_MESSAGE_CLICK = 'campaign-message-click';
export const ON_UNREAD_MESSAGE_CLICK = 'unread-message-click';
export const EXECUTE_CAMPAIGN = 'execute-campaign';
export const SNOOZE_CAMPAIGNS = 'snooze-campaigns';

// Widget events
export const ON_AGENT_MESSAGE_RECEIVED = 'agent-message-received';
export const ON_WIDGET_TOGGLE = 'widget-toggle';
export const SET_CAMPAIGN_READ_ON = 'set-campaign-read-on';
export const TOGGLE_BUBBLE = 'toggle-bubble';

// IFrame events
export const SET_UNREAD_MODE = 'set-unread-mode';
export const SET_IFRAME_HEIGHT = 'set-iframe-height';

// Widget visibility events
export const WIDGET_VISIBLE = 'widget-visible';
export const CHANGE_URL = 'change-url';

// SDK events
export const SDK_SET_BUBBLE_VISIBILITY = 'sdk-set-bubble-visibility';