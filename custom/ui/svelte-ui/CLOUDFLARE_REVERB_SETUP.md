# Cloudflare Proxy Configuration for Laravel Reverb

This guide explains how to configure Laravel Reverb WebSocket server when using Cloudflare as a proxy with SSL/TLS support.

## Overview

When using Cloudflare with proxy enabled (orange cloud), Cloudflare handles SSL/TLS termination and forwards requests to your origin server. This affects how you configure Laravel Reverb.

## Cloudflare WebSocket Support

Cloudflare supports WebSocket connections on all plans, including the free tier. However, you need to configure your setup correctly:

1. **WebSocket connections are automatically upgraded** when Cloudflare detects the WebSocket handshake
2. **SSL/TLS is terminated at Cloudflare**, then forwarded to your origin
3. **Your origin can use HTTP or HTTPS**, depending on your Cloudflare SSL/TLS mode

## Configuration

### Option 1: Cloudflare Flexible SSL (Origin uses HTTP)

This is the simplest setup where Cloudflare handles SSL/TLS, and your origin server uses plain HTTP.

**Laravel .env:**
```bash
# Application URL (what users see)
APP_URL=https://clearline.mdriaz.com.bd

# CORS - Add your frontend domain
CORS_ALLOWED_ORIGINS=https://clearlines.netlify.app

# Reverb Configuration (Origin server - HTTP)
REVERB_HOST=0.0.0.0                    # Listen on all interfaces
REVERB_PORT=8080                        # Internal port (not exposed publicly)
REVERB_SCHEME=http                      # Origin uses HTTP, Cloudflare adds HTTPS

# Reverb will receive connections via Cloudflare
# Cloudflare → HTTPS → Your Server (HTTP on port 8080)
```

**Cloudflare Settings:**
- SSL/TLS mode: **Flexible** (Cloudflare ↔ Visitors: HTTPS, Cloudflare ↔ Origin: HTTP)
- WebSocket: Enabled by default
- Proxy status: Enabled (orange cloud ☁️)

**Frontend Environment Variables:**
```bash
VITE_API_BASE_URL=https://clearline.mdriaz.com.bd
VITE_WS_URL=wss://clearline.mdriaz.com.bd/app
```

**Important:** Clients connect with `wss://` (secure WebSocket) because Cloudflare provides the SSL layer.

### Option 2: Cloudflare Full SSL (Origin uses HTTPS with self-signed cert)

If you want encryption between Cloudflare and your origin, use Full SSL mode.

**Laravel .env:**
```bash
APP_URL=https://clearline.mdriaz.com.bd
CORS_ALLOWED_ORIGINS=https://clearlines.netlify.app

# Reverb with HTTPS (requires SSL certificate on origin)
REVERB_HOST=0.0.0.0
REVERB_PORT=8443                        # HTTPS port
REVERB_SCHEME=https                     # Origin also uses HTTPS

# You'll need SSL certificate files
REVERB_TLS_CERT=/path/to/certificate.crt
REVERB_TLS_KEY=/path/to/private.key
```

**Cloudflare Settings:**
- SSL/TLS mode: **Full** or **Full (Strict)**
- Full: Accepts self-signed certificates
- Full (Strict): Requires valid SSL certificate

**Frontend Environment Variables:**
```bash
VITE_API_BASE_URL=https://clearline.mdriaz.com.bd
VITE_WS_URL=wss://clearline.mdriaz.com.bd/app
```

### Option 3: Direct Connection (Bypass Cloudflare for WebSocket)

If you experience issues with Cloudflare proxy, you can create a subdomain that bypasses Cloudflare for WebSocket:

**DNS Setup:**
- `api.clearline.mdriaz.com.bd` → Cloudflare proxy enabled (orange cloud ☁️)
- `ws.clearline.mdriaz.com.bd` → Cloudflare proxy disabled (gray cloud ☁️)

**Laravel .env:**
```bash
APP_URL=https://api.clearline.mdriaz.com.bd
CORS_ALLOWED_ORIGINS=https://clearlines.netlify.app

REVERB_HOST=ws.clearline.mdriaz.com.bd  # Direct connection
REVERB_PORT=443
REVERB_SCHEME=https
```

**Frontend Environment Variables:**
```bash
VITE_API_BASE_URL=https://api.clearline.mdriaz.com.bd
VITE_WS_URL=wss://ws.clearline.mdriaz.com.bd/app
```

**Note:** This requires your server to have a valid SSL certificate (Let's Encrypt recommended).

## Recommended Setup for Cloudflare

**Best Practice:** Use **Option 1 (Flexible SSL)** for simplicity:

1. Cloudflare handles all SSL/TLS
2. Your origin server uses HTTP (simpler configuration)
3. No need to manage SSL certificates on your server
4. Cloudflare automatically handles WebSocket upgrade

## Nginx Configuration

Your nginx should proxy WebSocket connections to Reverb:

```nginx
# For Cloudflare Flexible SSL (origin uses HTTP)
server {
    listen 80;
    server_name clearline.mdriaz.com.bd;
    
    # WebSocket proxy to Reverb
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;  # Important: Tell Reverb it's HTTPS
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 86400;
    }
    
    # API routes
    location / {
        proxy_pass http://127.0.0.1:8000;  # Laravel
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

## Cloudflare Configuration Checklist

- [ ] SSL/TLS mode set to **Flexible** (or Full if using HTTPS on origin)
- [ ] WebSocket support enabled (default)
- [ ] Proxy status enabled (orange cloud)
- [ ] Add frontend domain to CORS_ALLOWED_ORIGINS
- [ ] Configure `REVERB_SCHEME=http` for Flexible SSL
- [ ] Use `wss://` in frontend VITE_WS_URL
- [ ] Set `X-Forwarded-Proto https` in nginx

## Testing WebSocket Connection

1. Open browser DevTools → Network tab
2. Filter by "WS" (WebSocket)
3. You should see connection to `wss://clearline.mdriaz.com.bd/app`
4. Status should be "101 Switching Protocols"
5. Check Console for "WebSocket connected" message

## Troubleshooting

### Issue: WebSocket connection fails with 502 Bad Gateway

**Cause:** Reverb is not running or nginx can't reach it.

**Solution:**
```bash
# Check if Reverb is running
ps aux | grep reverb

# Start Reverb
php artisan reverb:start

# Check nginx logs
tail -f /var/log/nginx/error.log
```

### Issue: WebSocket connects but immediately disconnects

**Cause:** CORS or authentication issue.

**Solution:**
1. Verify `CORS_ALLOWED_ORIGINS` includes your frontend domain
2. Check browser console for CORS errors
3. Verify authentication token is being sent

### Issue: WebSocket works locally but not through Cloudflare

**Cause:** Cloudflare WebSocket timeout or configuration.

**Solution:**
1. Ensure WebSocket is enabled in Cloudflare dashboard
2. Check Cloudflare → Speed → Optimization → WebSocket is not blocked
3. Try disabling Cloudflare temporarily (gray cloud) to verify it's a Cloudflare issue

### Issue: SSL certificate errors

**Cause:** Mismatch between Cloudflare SSL mode and origin configuration.

**Solution:**
- Use **Flexible** SSL mode if origin uses HTTP
- Use **Full** SSL mode if origin uses self-signed certificate
- Use **Full (Strict)** only if origin has valid SSL certificate

## Security Considerations

1. **Always use wss:// (not ws://)** in production for frontend
2. **Set CORS_ALLOWED_ORIGINS** to specific domains (not *)
3. **Use Cloudflare's security features:**
   - Rate limiting
   - DDoS protection
   - WAF rules
4. **Keep Reverb behind reverse proxy** (nginx/Caddy), don't expose directly

## Performance Tips

1. **Enable Cloudflare Argo** for faster WebSocket connections (paid feature)
2. **Use Cloudflare's closest data center** to your origin server
3. **Set appropriate timeout values:**
   - Nginx: `proxy_read_timeout 86400` (24 hours)
   - Reverb: `max_connections` and `heartbeat_interval` settings

## Additional Resources

- [Cloudflare WebSocket Documentation](https://developers.cloudflare.com/fundamentals/get-started/concepts/how-cloudflare-works/#websockets)
- [Laravel Reverb Documentation](https://laravel.com/docs/11.x/reverb)
- [Nginx WebSocket Proxying](https://nginx.org/en/docs/http/websocket.html)
