-- Allow the same email address to own one customer and one management account.
-- Safe to run more than once. Back up the database before applying migrations.
USE globetrek_adventures;
DELIMITER $$ DROP PROCEDURE IF EXISTS migrate_users_email_scope $$ CREATE PROCEDURE migrate_users_email_scope() BEGIN IF EXISTS (
    SELECT 1
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND index_name = 'email'
) THEN
ALTER TABLE users DROP INDEX email;
END IF;
IF EXISTS (
    SELECT 1
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND index_name = 'uq_users_email_role'
) THEN
ALTER TABLE users DROP INDEX uq_users_email_role;
END IF;
IF NOT EXISTS (
    SELECT 1
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND column_name = 'account_scope'
) THEN
ALTER TABLE users
ADD COLUMN account_scope VARCHAR(10) GENERATED ALWAYS AS (
        CASE
            WHEN role = 'customer' THEN 'customer'
            ELSE 'management'
        END
    ) STORED
AFTER role;
END IF;
IF NOT EXISTS (
    SELECT 1
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND index_name = 'uq_users_email_scope'
) THEN
ALTER TABLE users
ADD UNIQUE KEY uq_users_email_scope (email, account_scope);
END IF;
END $$ CALL migrate_users_email_scope() $$ DROP PROCEDURE migrate_users_email_scope $$ DELIMITER;