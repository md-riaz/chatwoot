/**
 * Reports Constants
 * Defines constants used across reporting features
 */

export interface GroupByFilter {
  id: number;
  period: string;
  groupByKey?: string;
}

/**
 * Group by filter options for reports
 * Used to aggregate data by different time periods
 */
export const GROUP_BY_FILTER: Record<number, GroupByFilter> = {
  1: { id: 1, period: 'day', groupByKey: 'Day' },
  2: { id: 2, period: 'week', groupByKey: 'Week' },
  3: { id: 3, period: 'month', groupByKey: 'Month' },
  4: { id: 4, period: 'year', groupByKey: 'Year' },
};

/**
 * Report metric types
 */
export const REPORT_METRICS = {
  CONVERSATIONS: 'conversations_count',
  INCOMING_MESSAGES: 'incoming_messages_count',
  OUTGOING_MESSAGES: 'outgoing_messages_count',
  FIRST_RESPONSE_TIME: 'avg_first_response_time',
  RESOLUTION_TIME: 'avg_resolution_time',
  RESOLUTION_COUNT: 'resolutions_count',
  REPLY_TIME: 'reply_time',
} as const;

/**
 * Report types
 */
export const REPORT_TYPES = {
  ACCOUNT: 'account',
  AGENT: 'agent',
  LABEL: 'label',
  INBOX: 'inbox',
  TEAM: 'team',
} as const;

export type ReportType = (typeof REPORT_TYPES)[keyof typeof REPORT_TYPES];
export type ReportMetric = (typeof REPORT_METRICS)[keyof typeof REPORT_METRICS];
