import { defineConfig } from 'histoire';
import { HstSvelte } from '@histoire/plugin-svelte';

export default defineConfig({
  plugins: [HstSvelte()],
  setupFile: './src/histoire.setup.ts',
  vite: {
    server: {
      port: 6006
    }
  },
  theme: {
    darkClass: 'dark',
    title: '@chatwoot/svelte-ui',
    favicon: '/favicon.ico',
    logo: {
      square: '/logo.svg',
      light: '/logo.svg',
      dark: '/logo.svg'
    },
    colors: {
      primary: {
        50: '#eff6ff',
        100: '#dbeafe',
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',
        600: '#2563eb',
        700: '#1d4ed8',
        800: '#1e40af',
        900: '#1e3a8a'
      }
    }
  },
  defaultStoryProps: {
    icon: 'carbon:cube',
    iconColor: '#3b82f6',
    layout: {
      type: 'grid',
      width: '100%'
    }
  },
  tree: {
    groups: [
      {
        id: 'top',
        title: ''
      },
      {
        id: 'primitives',
        title: 'Primitives'
      },
      {
        id: 'components',
        title: 'Components'
      },
      {
        id: 'patterns',
        title: 'Patterns'
      }
    ]
  }
});
