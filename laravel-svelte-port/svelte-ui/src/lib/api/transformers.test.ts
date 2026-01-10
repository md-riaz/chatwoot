/**
 * Test file for API transformers
 * Tests the camelCase/snake_case conversion including feature flag arrays
 */

import { describe, it, expect } from 'vitest';
import { keysToSnake, keysToCamel, camelToSnake, snakeToCamel } from './transformers';

describe('API Transformers', () => {
  describe('String conversion', () => {
    it('converts camelCase to snake_case', () => {
      expect(camelToSnake('websiteWidget')).toBe('website_widget');
      expect(camelToSnake('emailIntegration')).toBe('email_integration');
      expect(camelToSnake('customRoles')).toBe('custom_roles');
      expect(camelToSnake('slaPolicies')).toBe('sla_policies');
      expect(camelToSnake('auditLogs')).toBe('audit_logs');
    });

    it('converts snake_case to camelCase', () => {
      expect(snakeToCamel('website_widget')).toBe('websiteWidget');
      expect(snakeToCamel('email_integration')).toBe('emailIntegration');
      expect(snakeToCamel('custom_roles')).toBe('customRoles');
      expect(snakeToCamel('sla_policies')).toBe('slaPolicies');
      expect(snakeToCamel('audit_logs')).toBe('auditLogs');
    });
  });

  describe('Object key transformation', () => {
    it('transforms object keys to snake_case', () => {
      const input = {
        selectedFeatureFlags: ['websiteWidget', 'emailIntegration'],
        customAttributes: { firstName: 'John' },
        userId: 123
      };

      const expected = {
        selected_feature_flags: ['website_widget', 'email_integration'],
        custom_attributes: { first_name: 'John' },
        user_id: 123
      };

      expect(keysToSnake(input)).toEqual(expected);
    });

    it('transforms object keys to camelCase', () => {
      const input = {
        selected_feature_flags: ['website_widget', 'email_integration'],
        custom_attributes: { first_name: 'John' },
        user_id: 123
      };

      const expected = {
        selectedFeatureFlags: ['websiteWidget', 'emailIntegration'],
        customAttributes: { firstName: 'John' },
        userId: 123
      };

      expect(keysToCamel(input)).toEqual(expected);
    });
  });

  describe('Feature flag array transformation', () => {
    it('transforms object keys but NOT array element values', () => {
      const input = {
        selectedFeatureFlags: [
          'inbound_emails',      // Backend sends snake_case values (from Rails features.yml)
          'channel_email', 
          'team_management',
          'custom_attributes',
          'help_center',
          'agent_bots'
        ]
      };

      const expected = {
        selected_feature_flags: [
          'inbound_emails',      // Array values stay as-is
          'channel_email',
          'team_management', 
          'custom_attributes',
          'help_center',
          'agent_bots'
        ]
      };

      expect(keysToSnake(input)).toEqual(expected);
    });

    it('transforms response keys but NOT array element values', () => {
      const input = {
        selected_feature_flags: [
          'inbound_emails',      // Backend returns snake_case values (from Rails features.yml)
          'channel_email',
          'team_management',
          'custom_attributes',
          'help_center',
          'agent_bots'
        ]
      };

      const expected = {
        selectedFeatureFlags: [
          'inbound_emails',      // Array values stay as-is (frontend must match these)
          'channel_email',
          'team_management',
          'custom_attributes',
          'help_center',
          'agent_bots'
        ]
      };

      expect(keysToCamel(input)).toEqual(expected);
    });

    it('does not transform array element strings', () => {
      const input = {
        tags: ['urgent', 'bug-fix', 'feature_request', 'API', 'user@example.com'],
        emails: ['user@example.com', 'admin@test.org']
      };

      const expected = {
        tags: ['urgent', 'bug-fix', 'feature_request', 'API', 'user@example.com'],
        emails: ['user@example.com', 'admin@test.org']
      };

      expect(keysToSnake(input)).toEqual(expected);
      expect(keysToCamel(input)).toEqual(expected);
    });
  });

  describe('Nested object transformation', () => {
    it('transforms nested objects and arrays recursively', () => {
      const input = {
        accountData: {
          selectedFeatureFlags: ['websiteWidget', 'customRoles'],
          userInfo: {
            firstName: 'John',
            lastName: 'Doe'
          }
        },
        metaData: {
          createdAt: '2024-01-01',
          updatedBy: 'admin'
        }
      };

      const expected = {
        account_data: {
          selected_feature_flags: ['website_widget', 'custom_roles'],
          user_info: {
            first_name: 'John',
            last_name: 'Doe'
          }
        },
        meta_data: {
          created_at: '2024-01-01',
          updated_by: 'admin'
        }
      };

      expect(keysToSnake(input)).toEqual(expected);
    });
  });

  describe('Edge cases', () => {
    it('handles null and undefined values', () => {
      expect(keysToSnake(null)).toBe(null);
      expect(keysToSnake(undefined)).toBe(undefined);
      expect(keysToCamel(null)).toBe(null);
      expect(keysToCamel(undefined)).toBe(undefined);
    });

    it('handles empty objects and arrays', () => {
      expect(keysToSnake({})).toEqual({});
      expect(keysToSnake([])).toEqual([]);
      expect(keysToCamel({})).toEqual({});
      expect(keysToCamel([])).toEqual([]);
    });

    it('handles primitive values', () => {
      expect(keysToSnake('test')).toBe('test');
      expect(keysToSnake(123)).toBe(123);
      expect(keysToSnake(true)).toBe(true);
      expect(keysToCamel('test')).toBe('test');
      expect(keysToCamel(123)).toBe(123);
      expect(keysToCamel(true)).toBe(true);
    });
  });
});