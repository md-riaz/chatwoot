# SPA Configuration & SSR/CSR Setup

**Date:** 2026-01-03  
**Status:** ✅ Configured as Pure SPA with Full API Integration

## Configuration Summary

The svelte-ui project is correctly configured as a **Single Page Application (SPA)** with:
- ✅ SSR (Server-Side Rendering) disabled globally
- ✅ CSR (Client-Side Rendering) enabled globally
- ✅ Full API integration via client-side HTTP calls
- ✅ Static file generation with fallback routing
- ✅ Page refresh will show correct page (handled by fallback)

## Configuration Files

### 1. Root Layout Configuration
**File:** `src/routes/+layout.ts`

```typescript
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
```

**Effect:** All routes inherit these settings, ensuring pure client-side rendering throughout the application.

### 2. Adapter Configuration
**File:** `svelte.config.js`

```javascript
adapter: adapter({
  fallback: 'index.html', // SPA mode - all routes serve index.html
  pages: 'build',
  assets: 'build',
  precompress: false,
  strict: true
}),
prerender: {
  entries: [] // No prerendering for SPA
}
```

**Effect:** 
- `fallback: 'index.html'` ensures all routes serve the same HTML file
- Client-side router handles navigation
- API calls are made from the browser

## Build Output

### What Gets Generated

When you run `pnpm run build`, you'll see:

1. **`.svelte-kit/output/server/`** - Build artifacts (not deployed)
   - These files are generated during the build process
   - They are NOT used at runtime with the static adapter
   - They are build-time only for bundling and optimization

2. **`build/`** - Actual deployment output
   ```
   build/
   ├── index.html          # Single HTML file for all routes
   └── _app/               # JavaScript bundles and assets
       └── immutable/
           ├── entry/      # App entry points
           ├── chunks/     # Code-split bundles
           └── assets/     # CSS, images, etc.
   ```

### Build Output Verification

```bash
$ ls build/
index.html  _app/

$ cat build/index.html
<!doctype html>
<html lang="en">
  <head>
    <link rel="modulepreload" href="/_app/immutable/entry/start.*.js">
    <link rel="modulepreload" href="/_app/immutable/entry/app.*.js">
    <!-- Pure client-side JavaScript modules -->
  </head>
  <body data-sveltekit-preload-data="hover">
    <div style="display: contents">
      <script>
        __sveltekit_* = {
          base: "",
          env: {}
        }
      </script>
    </div>
  </body>
</html>
```

**Key Observations:**
- ✅ No server-side rendered content
- ✅ All content loaded via JavaScript modules
- ✅ Single HTML file serves all routes
- ✅ Client-side router handles navigation

## How It Works

### 1. Initial Load
```
User requests: https://app.example.com/app/conversations
          ↓
Server: Serves index.html (regardless of path)
          ↓
Browser: Loads JavaScript bundles
          ↓
SvelteKit Router: Parses URL path
          ↓
Component: Renders /app/conversations
          ↓
API Call: Fetches data from backend API
```

### 2. Navigation
```
User clicks: Link to /app/reports
          ↓
SvelteKit Router: Intercepts navigation (no page reload)
          ↓
Component: Renders /app/reports
          ↓
API Call: Fetches reports data from API
```

### 3. Page Refresh
```
User refreshes: On /app/settings/profile
          ↓
Browser: Requests /app/settings/profile
          ↓
Server: Serves index.html (fallback)
          ↓
Browser: Loads JavaScript
          ↓
Router: Parses URL, shows /app/settings/profile
          ↓
✅ User sees correct page
```

## API Integration

All API calls are made client-side using the `ky` HTTP client:

**File:** `src/lib/api/client.ts`

```typescript
import ky from 'ky';

const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000';

export const api = ky.create({
  prefixUrl: baseUrl,
  timeout: 30000,
  hooks: {
    beforeRequest: [
      (request) => {
        const token = getAuthToken();
        if (token) {
          request.headers.set('Authorization', `Bearer ${token}`);
        }
      }
    ]
  }
});
```

**Key Points:**
- ✅ All API calls from browser (client-side)
- ✅ Authentication via localStorage and headers
- ✅ No server-side data fetching
- ✅ Full separation between frontend and backend

## Server Files in Build Log

**Question:** "Why do I see server files in the build log?"

**Answer:** The server files (`.svelte-kit/output/server/`) are build-time artifacts:

1. **Purpose:** Used during the build process for:
   - Code splitting analysis
   - Dependency resolution
   - Bundle optimization
   - Type checking

2. **Runtime:** These files are **NOT** used at runtime:
   - The static adapter doesn't deploy them
   - Only `build/` directory is deployed
   - All rendering happens in the browser

3. **Normal Behavior:** This is expected and correct for SvelteKit with `adapter-static`

## Deployment

### What to Deploy

Deploy only the `build/` directory:

```bash
# Build the project
pnpm run build

# Deploy build/ directory to your static hosting
# Examples:
# - Netlify: Deploy build/ folder
# - Vercel: Deploy build/ folder
# - Nginx: Serve build/ folder with fallback to index.html
# - S3 + CloudFront: Upload build/ with routing rules
```

### Server Configuration

Your web server must redirect all requests to `index.html`:

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

**Apache:**
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

**Vercel (vercel.json):**
```json
{
  "routes": [
    { "handle": "filesystem" },
    { "src": "/(.*)", "dest": "/index.html" }
  ]
}
```

## Environment Variables

Configure the API URL via environment variable:

**`.env`:**
```bash
VITE_API_BASE_URL=https://api.chatwoot.com
```

**`.env.production`:**
```bash
VITE_API_BASE_URL=https://api.production.chatwoot.com
```

The frontend will make all API calls to this base URL from the browser.

## Verification Checklist

✅ **SSR Disabled:** `export const ssr = false` in `src/routes/+layout.ts`

✅ **CSR Enabled:** `export const csr = true` in `src/routes/+layout.ts`

✅ **Static Adapter:** `adapter-static` with `fallback: 'index.html'`

✅ **Build Output:** Only `build/index.html` and `build/_app/` generated

✅ **No Prerendering:** `prerender: { entries: [] }`

✅ **API Integration:** All API calls via `ky` from browser

✅ **Page Refresh:** Works correctly due to fallback routing

✅ **Client-Side Router:** SvelteKit router handles all navigation

## Testing

### Test Page Refresh Behavior

1. Start the app:
   ```bash
   pnpm run dev
   ```

2. Navigate to a deep route:
   ```
   http://localhost:5173/app/settings/profile
   ```

3. Refresh the page (F5 or Cmd+R)

4. ✅ Expected: Page shows correctly without 404

### Test Build Output

1. Build the project:
   ```bash
   pnpm run build
   ```

2. Preview the build:
   ```bash
   pnpm run preview
   ```

3. Navigate and refresh on different routes

4. ✅ Expected: All routes work correctly

## Migration from Vue

The SPA configuration is compatible with Vue migration:

| Vue | SvelteKit |
|-----|-----------|
| Vue Router (history mode) | SvelteKit Router (client-side) |
| `mode: 'history'` | `fallback: 'index.html'` |
| Axios API calls | ky API calls |
| Vuex state | Svelte stores |
| No SSR (SPA only) | `ssr = false` |

## Conclusion

✅ **The svelte-ui project is correctly configured as a pure SPA:**

- SSR is disabled globally
- CSR is enabled globally
- All API calls are client-side
- Page refresh works correctly with fallback routing
- Server files in build log are normal build artifacts
- Only static files (build/) are deployed
- Fully compatible with Vue to SvelteKit migration

The configuration ensures the frontend can completely replace the Vue frontend as a standalone SPA with full API integration.

---

**References:**
- [SvelteKit Adapter Static](https://kit.svelte.dev/docs/adapter-static)
- [SvelteKit CSR and SSR](https://kit.svelte.dev/docs/page-options)
- [SvelteKit Single-Page Apps](https://kit.svelte.dev/docs/single-page-apps)
