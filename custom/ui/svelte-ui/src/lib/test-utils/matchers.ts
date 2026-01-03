import { expect } from 'vitest';
import type { MatcherResult } from 'vitest';

/**
 * Custom matcher to check if a value is a valid date string
 */
expect.extend({
  toBeValidDateString(received: any): MatcherResult {
    const pass = typeof received === 'string' && !isNaN(Date.parse(received));
    return {
      pass,
      message: () =>
        pass
          ? `Expected ${received} not to be a valid date string`
          : `Expected ${received} to be a valid date string`,
      actual: received,
      expected: 'valid date string'
    };
  },
  
  toBeWithinRange(received: number, floor: number, ceiling: number): MatcherResult {
    const pass = received >= floor && received <= ceiling;
    return {
      pass,
      message: () =>
        pass
          ? `Expected ${received} not to be within range ${floor} - ${ceiling}`
          : `Expected ${received} to be within range ${floor} - ${ceiling}`,
      actual: received,
      expected: `${floor} - ${ceiling}`
    };
  }
});

// Extend TypeScript types
declare module 'vitest' {
  interface Assertion {
    toBeValidDateString(): void;
    toBeWithinRange(floor: number, ceiling: number): void;
  }
}
