# ClearLine Laravel Documentation

This directory contains comprehensive documentation for the ClearLine Laravel API and system administration.

## Documentation Files

| File | Description |
|------|-------------|
| [API_DOCUMENTATION_COMPLETE.md](API_DOCUMENTATION_COMPLETE.md) | **Complete API reference** with all endpoints, authentication, and examples |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Original API reference with all endpoints, methods, and permissions |
| [AUTHORIZATION.md](AUTHORIZATION.md) | Authorization system, roles, policies, and middleware |
| [INTEGRATIONS.md](INTEGRATIONS.md) | Detailed guide for all channel and third-party integrations |
| [VOICE_CHANNEL_GUIDE.md](VOICE_CHANNEL_GUIDE.md) | **Complete voice channel guide** with Twilio integration, WebRTC, and call management |
| [EMAIL_CONFIGURATION.md](EMAIL_CONFIGURATION.md) | Email system configuration and customization |
| [openapi/](openapi/) | **OpenAPI 3.0 specifications** for all API endpoints |

## Deployment and Operations

| File | Description |
|------|-------------|
| [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) | **Complete deployment guide** for production environments |
| [RAILS_TO_LARAVEL_MIGRATION_GUIDE.md](RAILS_TO_LARAVEL_MIGRATION_GUIDE.md) | **Comprehensive migration guide** from Chatwoot Rails to ClearLine Laravel |
| [TROUBLESHOOTING_GUIDE.md](TROUBLESHOOTING_GUIDE.md) | **Troubleshooting guide** for common issues and debugging |
| [MAINTENANCE_GUIDE.md](MAINTENANCE_GUIDE.md) | **Maintenance and operations guide** for ongoing system management |

## OpenAPI Specification

The `openapi/` directory contains machine-readable API specifications that can be used for:

- **Interactive Documentation**: Import into Swagger UI or Redoc
- **API Testing**: Import into Postman with example data preloaded
- **SDK Generation**: Generate client libraries in any language
- **API Validation**: Validate requests and responses

See [openapi/README.md](openapi/README.md) for detailed usage instructions.

## Quick Links

### Getting Started
- See the main [README.md](../README.md) for project setup
- See [FOLDER_STRUCTURE.md](../FOLDER_STRUCTURE.md) for codebase organization
- See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for production deployment

### Migration and Operations
- **Migration**: [RAILS_TO_LARAVEL_MIGRATION_GUIDE.md](RAILS_TO_LARAVEL_MIGRATION_GUIDE.md) - Complete migration from Rails
- **Deployment**: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Production deployment procedures
- **Troubleshooting**: [TROUBLESHOOTING_GUIDE.md](TROUBLESHOOTING_GUIDE.md) - Common issues and solutions
- **Maintenance**: [MAINTENANCE_GUIDE.md](MAINTENANCE_GUIDE.md) - Ongoing system maintenance

### API Reference
- **Complete API Docs**: [API_DOCUMENTATION_COMPLETE.md](API_DOCUMENTATION_COMPLETE.md) - Comprehensive API reference
- **Authentication**: `/api/v1/auth/*`
- **Profile**: `/api/v1/profile/*`
- **Account Resources**: `/api/v1/accounts/{account}/*`
- **Widget API**: `/api/v1/widget/*`
- **Platform API**: `/api/v1/platform/*`
- **Public API**: `/api/v1/public/*`
- **Super Admin**: `/api/v1/super_admin/*` ⭐ **NEW**

### Authorization
- **EnsureAccountAccess**: Validates user has access to account
- **EnsureAccountAdmin**: Validates user is admin of account
- **EnsureSuperAdmin**: Validates user is super admin ⭐ **NEW**

### Permission Levels
| Role | Description |
|------|-------------|
| Agent (1) | Standard team member |
| Administrator (2) | Full account access |
| Super Admin | Platform-wide access ⭐ **NEW** |

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

4. **Super Admin API** (Super Admin Only) ⭐ **NEW**
   - Platform administration
   - System configuration
   - Global user management
   - Cache management
   - Audit logging
   - **100% Rails Parity**

## Super Admin Features ⭐

The Super Admin API provides comprehensive platform-level administration:

### Dashboard & Monitoring
- System overview with real-time metrics
- Growth analytics and trends
- System health monitoring
- Instance status and version info

### User & Account Management
- Global user administration
- Account lifecycle management
- Cross-account user relationships
- Role and permission management

### System Configuration
- Global settings management
- Installation configuration
- Platform app management
- Access token administration

### Advanced Operations
- Multi-level cache management
- Pattern-based cache clearing
- Comprehensive audit logging
- Bulk operations support
- Export functionality

### Key Benefits
- **100% Rails Parity**: Complete feature compatibility
- **Performance Optimized**: Intelligent caching and chunked operations
- **Security First**: Comprehensive audit trails and access control
- **Type Safe**: Full DTO and validation coverage
- **Production Ready**: Extensive test coverage and error handling

## Contributing

When adding new API endpoints:

1. Add the controller in `app/Http/Controllers/Api/V1/`
2. Add routes in `routes/api.php`
3. Use appropriate middleware:
   - `auth:sanctum` for authenticated routes
   - `EnsureAccountAccess::class` for account-scoped routes
   - `EnsureAccountAdmin::class` for admin-only routes
   - `EnsureSuperAdmin::class` for super admin routes ⭐ **NEW**
4. Update this documentation
5. Update OpenAPI specifications

---

**Last Updated:** 2025-01-02  
**Super Admin Implementation:** ✅ **COMPLETE**  
**Documentation Status:** ✅ **COMPREHENSIVE** - Complete deployment, migration, troubleshooting, and maintenance guides available
