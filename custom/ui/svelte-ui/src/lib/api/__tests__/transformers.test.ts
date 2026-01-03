/**
 * Tests for data transformation utilities
 */

import { describe, it, expect } from 'vitest';
import {
  camelToSnake,
  snakeToCamel,
  keysToSnake,
  keysToCamel,
  transformKeysTo
} from '../transformers';

describe('String Transformation', () => {
  it('converts camelCase to snake_case', () => {
    expect(camelToSnake('firstName')).toBe('first_name');
    expect(camelToSnake('createdAt')).toBe('created_at');
    expect(camelToSnake('userId')).toBe('user_id');
  });

  it('converts snake_case to camelCase', () => {
    expect(snakeToCamel('first_name')).toBe('firstName');
    expect(snakeToCamel('created_at')).toBe('createdAt');
    expect(snakeToCamel('user_id')).toBe('userId');
  });
});

describe('Object Key Transformation', () => {
  it('converts object keys to snake_case', () => {
    const input = {
      firstName: 'John',
      lastName: 'Doe',
      createdAt: '2024-01-01'
    };

    const expected = {
      first_name: 'John',
      last_name: 'Doe',
      created_at: '2024-01-01'
    };

    expect(keysToSnake(input)).toEqual(expected);
  });

  it('converts object keys to camelCase', () => {
    const input = {
      first_name: 'John',
      last_name: 'Doe',
      created_at: '2024-01-01'
    };

    const expected = {
      firstName: 'John',
      lastName: 'Doe',
      createdAt: '2024-01-01'
    };

    expect(keysToCamel(input)).toEqual(expected);
  });

  it('handles nested objects', () => {
    const input = {
      firstName: 'John',
      userProfile: {
        profilePicture: 'url',
        emailAddress: 'test@example.com'
      }
    };

    const expected = {
      first_name: 'John',
      user_profile: {
        profile_picture: 'url',
        email_address: 'test@example.com'
      }
    };

    expect(keysToSnake(input)).toEqual(expected);
  });

  it('handles arrays', () => {
    const input = {
      userList: [
        { firstName: 'John', userId: 1 },
        { firstName: 'Jane', userId: 2 }
      ]
    };

    const expected = {
      user_list: [
        { first_name: 'John', user_id: 1 },
        { first_name: 'Jane', user_id: 2 }
      ]
    };

    expect(keysToSnake(input)).toEqual(expected);
  });

  it('handles null and undefined', () => {
    expect(keysToSnake(null)).toBe(null);
    expect(keysToSnake(undefined)).toBe(undefined);
    expect(keysToCamel(null)).toBe(null);
    expect(keysToCamel(undefined)).toBe(undefined);
  });

  it('preserves primitive values', () => {
    expect(keysToSnake('string')).toBe('string');
    expect(keysToSnake(123)).toBe(123);
    expect(keysToSnake(true)).toBe(true);
  });
});

describe('Generic Transformer', () => {
  it('transforms to snake_case when format is "snake"', () => {
    const input = { firstName: 'John' };
    const expected = { first_name: 'John' };
    expect(transformKeysTo(input, 'snake')).toEqual(expected);
  });

  it('transforms to camelCase when format is "camel"', () => {
    const input = { first_name: 'John' };
    const expected = { firstName: 'John' };
    expect(transformKeysTo(input, 'camel')).toEqual(expected);
  });
});
