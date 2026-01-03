import { defineConfig } from 'vitest/config';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import path from 'path';

export default defineConfig({
  plugins: [svelte({ hot: !process.env.VITEST })],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./vitest.setup.ts'],
    include: ['src/**/*.{test,spec}.{js,ts}'],
    exclude: ['node_modules', 'dist', '.svelte-kit'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html', 'lcov'],
      exclude: [
        'node_modules/',
        'src/**/*.d.ts',
        'src/**/*.config.ts',
        'src/**/types.ts',
        'src/**/__tests__/**',
        'src/**/index.ts', // Barrel exports
        '.svelte-kit/',
        'histoire/**'
      ],
      all: true,
      lines: 80,
      functions: 80,
      branches: 80,
      statements: 80
    },
    mockReset: true,
    restoreMocks: true,
    clearMocks: true
  },
  resolve: {
    alias: {
      $lib: path.resolve(__dirname, './src/lib'),
      $app: path.resolve(__dirname, './.svelte-kit/runtime/app')
    }
  }
});
