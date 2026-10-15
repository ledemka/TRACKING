-- Migration 021: Ajout de la langue préférée du destinataire sur l'expédition (fr, en, de, nl)
-- Date: 2026-10-15
-- Auteur: Équipe TRACKING

-- ============================================================================
-- UP: Application de la migration
-- ============================================================================
ALTER TABLE `shipments`
ADD COLUMN `recipient_lang` VARCHAR(10) NULL DEFAULT NULL
AFTER `recipient_email`;

INSERT INTO `schema_migrations` (`version`, `applied_at`)
VALUES ('021_add_shipment_recipient_lang', NOW())
ON DUPLICATE KEY UPDATE `applied_at` = NOW();

-- ============================================================================
-- DOWN: Procédure de retour arrière (Rollback)
-- ============================================================================
-- ALTER TABLE `shipments` DROP COLUMN `recipient_lang`;
-- DELETE FROM `schema_migrations` WHERE `version` = '021_add_shipment_recipient_lang';
