import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    environment: 'jsdom',
    include: [
      'src/**/*.test.ts',
      'src/**/*.spec.ts',
      'src/**/__tests__/**/*.ts'
    ],
    exclude: [
      '**/node_modules/**',
      '**/dist/**'
    ],
    globals: true,
    setupFiles: ['./src/lib/entities/__tests__/setup.ts']
  },
  resolve: {
    alias: {
      $lib: './src/lib'
    }
  }
});