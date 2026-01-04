# WebSocket Configuration Guide

This guide explains how to configure WebSocket connections for real-time communication between the Svelte UI and Laravel backend using Laravel Reverb.

## Overview

The Svelte UI connects to the Laravel backend via WebSocket for real-time updates (new messages, notifications, etc.). The backend uses Laravel Reverb as the WebSocket server.

## Environment Variables

### Frontend (Svelte UI)

Create a `.env` file in `custom/ui/svelte-ui/` with the following:

```bash
# Backend API URL
VITE_API_BASE_URL=https://your-domain.com

# WebSocket URL for Laravel Reverb
# Format: wss://your-domain.com/app (production with TLS)
#         ws://localhost:8080/app (local development)
VITE_WS_URL=wss://your-domain.com/app
```

**Important Notes:**
- Use `wss://` (WebSocket Secure) for production deployments with HTTPS
- Use `ws://` only for local development without TLS
- The `/app` path is the default Laravel Reverb endpoint
- If `VITE_WS_URL` is not set, the app will construct it from `VITE_API_BASE_URL`

### Backend (Laravel)

Configure the following in your Laravel `.env` file:

```bash
# CORS - Add your frontend domain(s)
CORS_ALLOWED_ORIGINS=https://yourapp.netlify.app,https://your-domain.com

# Laravel Reverb Configuration
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=your-domain.com  # Your production domain
REVERB_PORT=443               # 443 for HTTPS, 8080 for HTTP
REVERB_SCHEME=https           # https for production, http for development
```

## Common Issues and Solutions

### Issue: WebSocket trying to connect to localhost

**Problem:** 
```
WebSocket connection to 'ws://localhost:8000/cable?token=...' failed
```

**Solution:**
Set the `VITE_WS_URL` environment variable in your frontend `.env` file:
```bash
VITE_WS_URL=wss://your-domain.com/app
```

### Issue: CORS policy error

**Problem:**
```
Access to fetch at 'https://your-domain.com/api/...' from origin 'https://yourapp.netlify.app' 
has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present
```

**Solution:**
1. Add your frontend domain to `CORS_ALLOWED_ORIGINS` in the Laravel `.env` file:
   ```bash
   CORS_ALLOWED_ORIGINS=https://yourapp.netlify.app
   ```

2. Restart your Laravel application to apply the changes.

### Issue: WebSocket not using TLS (wss://)

**Problem:**
WebSocket connects with `ws://` instead of `wss://` in production.

**Solution:**
1. Ensure `VITE_WS_URL` uses `wss://` protocol:
   ```bash
   VITE_WS_URL=wss://your-domain.com/app
   ```

2. Verify Laravel Reverb is configured for HTTPS:
   ```bash
   REVERB_SCHEME=https
   REVERB_PORT=443
   ```

3. Ensure your nginx/reverse proxy is properly configured for WebSocket proxying (see nginx configuration below).

## Nginx Configuration

Your nginx configuration should include WebSocket support. **Note:** CORS for WebSocket connections is handled by Laravel Reverb server, not nginx.

```nginx
# WebSocket proxy for Laravel Reverb
location /app {
    proxy_pass http://websocket;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
    proxy_read_timeout 86400;
}
```

**Important:** Do not add CORS headers in nginx for WebSocket endpoints. Laravel Reverb handles CORS internally based on your `CORS_ALLOWED_ORIGINS` environment variable.

## Development Setup

For local development:

1. **Frontend `.env`:**
   ```bash
   VITE_API_BASE_URL=http://localhost:8000
   VITE_WS_URL=ws://localhost:8080/app
   ```

2. **Backend `.env`:**
   ```bash
   CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000
   REVERB_HOST=0.0.0.0
   REVERB_PORT=8080
   REVERB_SCHEME=http
   ```

3. Start Laravel Reverb:
   ```bash
   php artisan reverb:start
   ```

## Production Deployment

For production deployment (e.g., Netlify frontend + Laravel backend):

1. **Frontend `.env` (Netlify environment variables):**
   ```bash
   VITE_API_BASE_URL=https://api.your-domain.com
   VITE_WS_URL=wss://api.your-domain.com/app
   ```

2. **Backend `.env`:**
   ```bash
   CORS_ALLOWED_ORIGINS=https://yourapp.netlify.app
   REVERB_HOST=api.your-domain.com
   REVERB_PORT=443
   REVERB_SCHEME=https
   ```

3. Deploy and verify:
   - Check browser console for WebSocket connection status
   - Verify no CORS errors
   - Test real-time updates

## Testing WebSocket Connection

Open your browser's developer console and look for:

**Successful connection:**
```
WebSocket connected
WebSocket welcome received
```

**Connection issues:**
```
WebSocket connection error: ...
WebSocket closed: ...
```

## Additional Resources

- [Laravel Reverb Documentation](https://laravel.com/docs/11.x/reverb)
- [WebSocket API (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/WebSocket)
- [CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
