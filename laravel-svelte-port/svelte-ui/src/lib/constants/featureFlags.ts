/**
 * Feature Flags Constants
 * Defines feature flags used across the application
 */

/**
 * Available feature flags in the system
 */
export const FEATURE_FLAGS = {
  // Team Management
  TEAM_MANAGEMENT: 'team_management',
  
  // Agent Management
  AGENT_MANAGEMENT: 'agent_management',
  AGENT_BOT: 'agent_bot',
  
  // Reporting
  REPORTS: 'reports',
  CSAT_REPORTS: 'csat_reports',
  SLA_REPORTS: 'sla_reports',
  
  // Integrations
  INTEGRATIONS: 'integrations',
  WEBHOOKS: 'webhooks',
  
  // Help Center
  HELP_CENTER: 'help_center',
  
  // Automation
  AUTOMATIONS: 'automations',
  MACROS: 'macros',
  
  // Campaigns
  CAMPAIGNS: 'campaigns',
  
  // Custom Attributes
  CUSTOM_ATTRIBUTES: 'custom_attributes',
  
  // Labels
  LABELS: 'labels',
  
  // Audit Logs
  AUDIT_LOGS: 'audit_logs',
  
  // Custom Views
  CUSTOM_VIEWS: 'custom_views',
  
  // IP Lookup
  IP_LOOKUP: 'ip_lookup',
} as const;

export type FeatureFlag = (typeof FEATURE_FLAGS)[keyof typeof FEATURE_FLAGS];

/**
 * Feature flag descriptions for documentation
 */
export const FEATURE_FLAG_DESCRIPTIONS: Record<FeatureFlag, string> = {
  [FEATURE_FLAGS.TEAM_MANAGEMENT]: 'Manage teams and team assignments',
  [FEATURE_FLAGS.AGENT_MANAGEMENT]: 'Manage agents and their permissions',
  [FEATURE_FLAGS.AGENT_BOT]: 'Enable bot agents for automation',
  [FEATURE_FLAGS.REPORTS]: 'Access reporting features',
  [FEATURE_FLAGS.CSAT_REPORTS]: 'Customer satisfaction reports',
  [FEATURE_FLAGS.SLA_REPORTS]: 'Service level agreement reports',
  [FEATURE_FLAGS.INTEGRATIONS]: 'Third-party integrations',
  [FEATURE_FLAGS.WEBHOOKS]: 'Webhook configurations',
  [FEATURE_FLAGS.HELP_CENTER]: 'Knowledge base and help center',
  [FEATURE_FLAGS.AUTOMATIONS]: 'Workflow automations',
  [FEATURE_FLAGS.MACROS]: 'Saved response macros',
  [FEATURE_FLAGS.CAMPAIGNS]: 'Marketing campaigns',
  [FEATURE_FLAGS.CUSTOM_ATTRIBUTES]: 'Custom contact and conversation attributes',
  [FEATURE_FLAGS.LABELS]: 'Conversation and contact labels',
  [FEATURE_FLAGS.AUDIT_LOGS]: 'Activity audit logs',
  [FEATURE_FLAGS.CUSTOM_VIEWS]: 'Custom conversation views',
  [FEATURE_FLAGS.IP_LOOKUP]: 'IP address geolocation lookup',
};
