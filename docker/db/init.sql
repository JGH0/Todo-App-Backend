-- ─────────────────────────────────────────────────────────────────────────────
-- Database initialisation – runs on first container start
-- Creates the database (MariaDB handles CREATE DATABASE via MARIADB_DATABASE)
-- and ensures a clean state for migrations.
-- ─────────────────────────────────────────────────────────────────────────────

-- Migrations are run by the backend entrypoint (php spark migrate).
-- This file exists as a hook for any database-level prep work.
-- Currently empty – the schema is migration-managed.
