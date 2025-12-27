# ClearLine Laravel Documentation

This directory contains comprehensive documentation for the ClearLine Laravel API.

## Documentation Files

| File | Description |
|------|-------------|
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Complete API reference with all endpoints, methods, and permissions |
| [AUTHORIZATION.md](AUTHORIZATION.md) | Authorization system, roles, policies, and middleware |
| [INTEGRATIONS.md](INTEGRATIONS.md) | Detailed guide for all channel and third-party integrations |

## Quick Links

### Getting Started
- See the main [README.md](../README.md) for project setup
- See [FOLDER_STRUCTURE.md](../FOLDER_STRUCTURE.md) for codebase organization

### API Reference
- **Authentication**: `/api/v1/auth/*`
- **Profile**: `/api/v1/profile/*`
- **Account Resources**: `/api/v1/accounts/{account}/*`
- **Widget API**: `/api/v1/widget/*`
- **Platform API**: `/api/v1/platform/*`
- **Public API**: `/api/v1/public/*`
- **Super Admin**: `/api/v1/super_admin/*`

### Authorization
- **EnsureAccountAccess**: Validates user has access to account
- **EnsureAccountAdmin**: Validates user is admin of account
- **EnsureSuperAdmin**: Validates user is super admin

### Permission Levels
| Role | Description |
|------|-------------|
| Agent (1) | Standard team member |
| Administrator (2) | Full account access |
| Super Admin | Platform-wide access |

## API Sections

1. **Core APIs** (Authenticated)
   - Conversations, Messages, Contacts
   - Inboxes, Teams, Labels
   - Campaigns, Automation Rules
   - Canned Responses, Macros

2. **Widget API** (Public)
   - Chat widget endpoints
   - Uses X-Auth-Token header

3. **Platform API** (Platform Key)
   - SSO integration
   - Multi-tenant management

4. **Super Admin API** (Super Admin Only)
   - Platform administration
   - System configuration

## Contributing

When adding new API endpoints:

1. Add the controller in `app/Http/Controllers/Api/V1/`
2. Add routes in `routes/api.php`
3. Use appropriate middleware:
   - `auth:sanctum` for authenticated routes
   - `EnsureAccountAccess::class` for account-scoped routes
   - `EnsureAccountAdmin::class` for admin-only routes
   - `EnsureSuperAdmin::class` for super admin routes
4. Update this documentation

---

**Last Updated:** 2025-12-27
