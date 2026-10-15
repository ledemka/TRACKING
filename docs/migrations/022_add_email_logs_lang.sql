-- Migration 022: Ajout de la langue d'envoi dans l'historique des emails (email_logs)
-- Date: 2026-10-15
-- Auteur: Équipe TRACKING

-- ============================================================================
-- UP: Application de la migration
-- ============================================================================
ALTER TABLE `email_logs`
ADD COLUMN `email_lang` VARCHAR(10) NULL DEFAULT NULL
AFTER `created_at`;

INSERT INTO `schema_migrations` (`version`, `applied_at`)
VALUES ('022_add_email_logs_lang', NOW())
ON DUPLICATE KEY UPDATE `applied_at` = NOW();

-- ============================================================================
-- DOWN: Procédure de retour arrière (Rollback)
-- ============================================================================
-- ALTER TABLE `email_logs` DROP COLUMN `email_lang`;
-- DELETE FROM `schema_migrations` WHERE `version` = '022_add_email_logs_lang';
