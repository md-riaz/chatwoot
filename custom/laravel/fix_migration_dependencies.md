# 🔧 DEPENDENCY FIX APPLIED

## ✅ **Issues Fixed:**

1. **Portals Dependency**: Moved `portals` table creation from position 38 to position 23 (before `inboxes`)
2. **SLA Policies Dependency**: Moved `sla_policies` table creation from position 31 to position 26 (before `conversations`)

## 📋 **New Migration Order:**

```
000000 - Laravel Framework Tables (cache, jobs, sessions)
000001 - Users
000002 - Accounts  
000003 - Companies
000004 - Custom Roles
000005 - Agent Capacity Policies
000006 - Account Users
000007 - Contacts
000008 - Teams
000009 - Team Members
000010 - Labels
000011-022 - All Channel Tables
000023 - Portals ⬅️ MOVED HERE (was 038)
000024 - Inboxes (now can reference portals)
000025 - Contact Inboxes
000026 - SLA Policies ⬅️ MOVED HERE (was 031)
000027 - Conversations (now can reference sla_policies)
000028 - Messages
000029 - Agent Bots
000030 - Agent Bot Inboxes
000031 - Campaigns ⬅️ MOVED HERE (was 026)
000032+ - All other tables...
```

## 🚀 **Now Run This:**

```bash
cd custom/laravel

# Reset and run migrations with fixed dependencies
php artisan migrate:fresh

# Check status
php artisan migrate:status
```

## ✅ **Expected Result:**

All migrations should now run successfully without foreign key constraint errors because:
- `portals` table exists before `inboxes` tries to reference it
- `sla_policies` table exists before `conversations` tries to reference it

The dependency chain is now correct! 🎯