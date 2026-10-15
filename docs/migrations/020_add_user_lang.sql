-- Migration 020: Ajout de la préférence de langue pour les utilisateurs / admins (fr, en, de, nl)
-- Date: 2026-10-15
-- Auteur: Équipe TRACKING

-- ============================================================================
-- UP: Application de la migration
-- ============================================================================
ALTER TABLE `users`
ADD COLUMN `lang` VARCHAR(10) NULL DEFAULT NULL
AFTER `role`;

INSERT INTO `schema_migrations` (`version`, `applied_at`)
VALUES ('020_add_user_lang', NOW())
ON DUPLICATE KEY UPDATE `applied_at` = NOW();

-- ============================================================================
-- DOWN: Procédure de retour arrière (Rollback)
-- ============================================================================
-- ALTER TABLE `users` DROP COLUMN `lang`;
-- DELETE FROM `schema_migrations` WHERE `version` = '020_add_user_lang';
