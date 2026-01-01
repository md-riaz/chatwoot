import adapter from '@sveltejs/adapter-static';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	preprocess: vitePreprocess(),

	kit: {
		// Configured for SPA mode
		adapter: adapter({
			pages: 'build',
			assets: 'build',
			fallback: 'index.html', // Enable SPA mode
			precompress: false,
			strict: true
		}),
		prerender: {
			entries: [] // Disable prerendering for SPA
		}
	}
};

export default config;
