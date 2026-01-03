/**
 * Automation Rules Constants
 * Defines all condition types, operators, action types, and related configurations
 */

export const CONDITION_TYPES = {
  // Message conditions
  MESSAGE_CREATED: 'message_created',
  MESSAGE_UPDATED: 'message_updated',
  
  // Conversation conditions
  CONVERSATION_CREATED: 'conversation_created',
  CONVERSATION_UPDATED: 'conversation_updated',
  CONVERSATION_OPENED: 'conversation_opened',
  CONVERSATION_RESOLVED: 'conversation_resolved',
  
  // Contact conditions
  CONTACT_CREATED: 'contact_created',
  CONTACT_UPDATED: 'contact_updated'
} as const;

export const ATTRIBUTE_KEYS = {
  // Conversation attributes
  STATUS: 'status',
  ASSIGNEE_ID: 'assignee_id',
  TEAM_ID: 'team_id',
  INBOX_ID: 'inbox_id',
  PRIORITY: 'priority',
  BROWSER_LANGUAGE: 'browser_language',
  COUNTRY_CODE: 'country_code',
  REFERER: 'referer',
  
  // Message attributes
  MESSAGE_TYPE: 'message_type',
  CONTENT: 'content',
  EMAIL_SUBJECT: 'email_subject',
  
  // Contact attributes
  EMAIL: 'email',
  PHONE_NUMBER: 'phone_number',
  NAME: 'name',
  CITY: 'city',
  COUNTRY: 'country',
  COMPANY_NAME: 'company_name'
} as const;

export const OPERATORS = {
  // Equality
  EQUAL_TO: 'equal_to',
  NOT_EQUAL_TO: 'not_equal_to',
  
  // Comparison
  LESS_THAN: 'less_than',
  GREATER_THAN: 'greater_than',
  
  // String matching
  CONTAINS: 'contains',
  DOES_NOT_CONTAIN: 'does_not_contain',
  STARTS_WITH: 'starts_with',
  ENDS_WITH: 'ends_with',
  
  // Presence
  IS_PRESENT: 'is_present',
  IS_NOT_PRESENT: 'is_not_present',
  
  // Array
  IS_ANY_OF: 'is_any_of',
  IS_NOT_ANY_OF: 'is_not_any_of'
} as const;

export const ACTION_TYPES = {
  ASSIGN_AGENT: 'assign_agent',
  ASSIGN_TEAM: 'assign_team',
  ADD_LABEL: 'add_label',
  REMOVE_LABEL: 'remove_label',
  SEND_EMAIL_TO_TEAM: 'send_email_to_team',
  SEND_MESSAGE: 'send_message',
  SEND_WEBHOOK_EVENT: 'send_webhook_event',
  SEND_ATTACHMENT: 'send_attachment',
  MUTE_CONVERSATION: 'mute_conversation',
  SNOOZE_CONVERSATION: 'snooze_conversation',
  RESOLVE_CONVERSATION: 'resolve_conversation',
  CHANGE_PRIORITY: 'change_priority',
  ADD_PRIVATE_NOTE: 'add_private_note'
} as const;

export const OPERATOR_LABELS: Record<string, string> = {
  [OPERATORS.EQUAL_TO]: 'is equal to',
  [OPERATORS.NOT_EQUAL_TO]: 'is not equal to',
  [OPERATORS.LESS_THAN]: 'is less than',
  [OPERATORS.GREATER_THAN]: 'is greater than',
  [OPERATORS.CONTAINS]: 'contains',
  [OPERATORS.DOES_NOT_CONTAIN]: 'does not contain',
  [OPERATORS.STARTS_WITH]: 'starts with',
  [OPERATORS.ENDS_WITH]: 'ends with',
  [OPERATORS.IS_PRESENT]: 'is present',
  [OPERATORS.IS_NOT_PRESENT]: 'is not present',
  [OPERATORS.IS_ANY_OF]: 'is any of',
  [OPERATORS.IS_NOT_ANY_OF]: 'is not any of'
};

export const ACTION_LABELS: Record<string, string> = {
  [ACTION_TYPES.ASSIGN_AGENT]: 'Assign to agent',
  [ACTION_TYPES.ASSIGN_TEAM]: 'Assign to team',
  [ACTION_TYPES.ADD_LABEL]: 'Add label',
  [ACTION_TYPES.REMOVE_LABEL]: 'Remove label',
  [ACTION_TYPES.SEND_EMAIL_TO_TEAM]: 'Send email to team',
  [ACTION_TYPES.SEND_MESSAGE]: 'Send message',
  [ACTION_TYPES.SEND_WEBHOOK_EVENT]: 'Send webhook event',
  [ACTION_TYPES.SEND_ATTACHMENT]: 'Send attachment',
  [ACTION_TYPES.MUTE_CONVERSATION]: 'Mute conversation',
  [ACTION_TYPES.SNOOZE_CONVERSATION]: 'Snooze conversation',
  [ACTION_TYPES.RESOLVE_CONVERSATION]: 'Resolve conversation',
  [ACTION_TYPES.CHANGE_PRIORITY]: 'Change priority',
  [ACTION_TYPES.ADD_PRIVATE_NOTE]: 'Add private note'
};

export const CONDITION_TYPE_LABELS: Record<string, string> = {
  [CONDITION_TYPES.MESSAGE_CREATED]: 'Message is created',
  [CONDITION_TYPES.MESSAGE_UPDATED]: 'Message is updated',
  [CONDITION_TYPES.CONVERSATION_CREATED]: 'Conversation is created',
  [CONDITION_TYPES.CONVERSATION_UPDATED]: 'Conversation is updated',
  [CONDITION_TYPES.CONVERSATION_OPENED]: 'Conversation is opened',
  [CONDITION_TYPES.CONVERSATION_RESOLVED]: 'Conversation is resolved',
  [CONDITION_TYPES.CONTACT_CREATED]: 'Contact is created',
  [CONDITION_TYPES.CONTACT_UPDATED]: 'Contact is updated'
};

export const ATTRIBUTE_KEY_LABELS: Record<string, string> = {
  [ATTRIBUTE_KEYS.STATUS]: 'Status',
  [ATTRIBUTE_KEYS.ASSIGNEE_ID]: 'Assignee',
  [ATTRIBUTE_KEYS.TEAM_ID]: 'Team',
  [ATTRIBUTE_KEYS.INBOX_ID]: 'Inbox',
  [ATTRIBUTE_KEYS.PRIORITY]: 'Priority',
  [ATTRIBUTE_KEYS.BROWSER_LANGUAGE]: 'Browser Language',
  [ATTRIBUTE_KEYS.COUNTRY_CODE]: 'Country',
  [ATTRIBUTE_KEYS.REFERER]: 'Referrer URL',
  [ATTRIBUTE_KEYS.MESSAGE_TYPE]: 'Message Type',
  [ATTRIBUTE_KEYS.CONTENT]: 'Message Content',
  [ATTRIBUTE_KEYS.EMAIL_SUBJECT]: 'Email Subject',
  [ATTRIBUTE_KEYS.EMAIL]: 'Email',
  [ATTRIBUTE_KEYS.PHONE_NUMBER]: 'Phone Number',
  [ATTRIBUTE_KEYS.NAME]: 'Name',
  [ATTRIBUTE_KEYS.CITY]: 'City',
  [ATTRIBUTE_KEYS.COUNTRY]: 'Country',
  [ATTRIBUTE_KEYS.COMPANY_NAME]: 'Company Name'
};

export const PRIORITY_OPTIONS = [
  { value: 'urgent', label: 'Urgent' },
  { value: 'high', label: 'High' },
  { value: 'medium', label: 'Medium' },
  { value: 'low', label: 'Low' },
  { value: null, label: 'None' }
];

export const STATUS_OPTIONS = [
  { value: 'open', label: 'Open' },
  { value: 'resolved', label: 'Resolved' },
  { value: 'pending', label: 'Pending' },
  { value: 'snoozed', label: 'Snoozed' }
];

export const MESSAGE_TYPE_OPTIONS = [
  { value: 0, label: 'Incoming' },
  { value: 1, label: 'Outgoing' },
  { value: 2, label: 'Activity' },
  { value: 3, label: 'Template' }
];

/**
 * Get operators applicable to a specific attribute type
 */
export function getOperatorsForAttribute(attributeKey: string): string[] {
  const stringOperators = [
    OPERATORS.EQUAL_TO,
    OPERATORS.NOT_EQUAL_TO,
    OPERATORS.CONTAINS,
    OPERATORS.DOES_NOT_CONTAIN,
    OPERATORS.STARTS_WITH,
    OPERATORS.ENDS_WITH,
    OPERATORS.IS_PRESENT,
    OPERATORS.IS_NOT_PRESENT
  ];

  const numberOperators = [
    OPERATORS.EQUAL_TO,
    OPERATORS.NOT_EQUAL_TO,
    OPERATORS.LESS_THAN,
    OPERATORS.GREATER_THAN,
    OPERATORS.IS_PRESENT,
    OPERATORS.IS_NOT_PRESENT
  ];

  const selectOperators = [
    OPERATORS.EQUAL_TO,
    OPERATORS.NOT_EQUAL_TO,
    OPERATORS.IS_ANY_OF,
    OPERATORS.IS_NOT_ANY_OF,
    OPERATORS.IS_PRESENT,
    OPERATORS.IS_NOT_PRESENT
  ];

  // Determine attribute type
  if ([ATTRIBUTE_KEYS.ASSIGNEE_ID, ATTRIBUTE_KEYS.TEAM_ID, ATTRIBUTE_KEYS.INBOX_ID, 
       ATTRIBUTE_KEYS.STATUS, ATTRIBUTE_KEYS.PRIORITY, ATTRIBUTE_KEYS.MESSAGE_TYPE].includes(attributeKey as any)) {
    return selectOperators;
  }

  if ([ATTRIBUTE_KEYS.CONTENT, ATTRIBUTE_KEYS.EMAIL, ATTRIBUTE_KEYS.NAME, 
       ATTRIBUTE_KEYS.CITY, ATTRIBUTE_KEYS.COUNTRY, ATTRIBUTE_KEYS.EMAIL_SUBJECT].includes(attributeKey as any)) {
    return stringOperators;
  }

  return stringOperators; // Default
}

/**
 * Validate automation rule
 */
export function validateAutomation(
  name: string,
  eventName: string,
  conditions: any[],
  actions: any[]
): { valid: boolean; errors: string[] } {
  const errors: string[] = [];

  if (!name || name.trim().length === 0) {
    errors.push('Name is required');
  }

  if (!eventName) {
    errors.push('Event type is required');
  }

  if (conditions.length === 0) {
    errors.push('At least one condition is required');
  }

  if (actions.length === 0) {
    errors.push('At least one action is required');
  }

  // Validate each condition
  conditions.forEach((condition, index) => {
    if (!condition.attributeKey) {
      errors.push(`Condition ${index + 1}: Attribute is required`);
    }
    if (!condition.filterOperator) {
      errors.push(`Condition ${index + 1}: Operator is required`);
    }
    if (!condition.values || condition.values.length === 0) {
      const needsValue = ![OPERATORS.IS_PRESENT, OPERATORS.IS_NOT_PRESENT].includes(condition.filterOperator);
      if (needsValue) {
        errors.push(`Condition ${index + 1}: Value is required`);
      }
    }
  });

  // Validate each action
  actions.forEach((action, index) => {
    if (!action.actionName) {
      errors.push(`Action ${index + 1}: Action type is required`);
    }
  });

  return {
    valid: errors.length === 0,
    errors
  };
}
