/**
 * Root Layout Configuration
 * Disables SSR globally for SPA mode with full CSR
 */

// Disable SSR globally - this is a pure SPA with API integration
export const ssr = false;

// Enable client-side rendering
export const csr = true;

// Prerendering is disabled in svelte.config.js
export const prerender = false;
