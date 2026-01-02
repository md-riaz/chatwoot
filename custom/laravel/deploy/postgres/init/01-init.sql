-- PostgreSQL initialization script for ClearLine Laravel

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- Set timezone
SET timezone = 'UTC';

-- Create additional database user for read-only access (optional)
-- CREATE USER clearline_readonly WITH PASSWORD 'readonly_password';
-- GRANT CONNECT ON DATABASE clearline_production TO clearline_readonly;
-- GRANT USAGE ON SCHEMA public TO clearline_readonly;
-- GRANT SELECT ON ALL TABLES IN SCHEMA public TO clearline_readonly;
-- ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO clearline_readonly;

-- Performance optimizations
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET checkpoint_completion_target = 0.9;
ALTER SYSTEM SET wal_buffers = '16MB';
ALTER SYSTEM SET default_statistics_target = 100;
ALTER SYSTEM SET random_page_cost = 1.1;
ALTER SYSTEM SET effective_io_concurrency = 200;
ALTER SYSTEM SET work_mem = '4MB';
ALTER SYSTEM SET min_wal_size = '1GB';
ALTER SYSTEM SET max_wal_size = '4GB';

-- Reload configuration
SELECT pg_reload_conf();