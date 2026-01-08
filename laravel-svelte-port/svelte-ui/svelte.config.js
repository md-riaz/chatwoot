import adapter from '@sveltejs/adapter-static';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
  preprocess: vitePreprocess(),
  kit: {
    adapter: adapter({
      fallback: 'index.html', // SPA mode
      pages: 'build',
      assets: 'build',
      precompress: false,
      strict: true
    }),
    prerender: {
      entries: [] // No prerendering for SPA
    },
    alias: {
      $lib: './src/lib',
      '$lib/*': './src/lib/*'
    }
  }
};

export default config;
