-- Migration 019: Ajout du mode de transport (air, land, sea)
-- Date: 2026-10-14
-- Auteur: Équipe TRACKING

-- ============================================================================
-- UP: Application de la migration
-- ============================================================================
-- 1. Ajout de la colonne transport_mode avec 'land' comme valeur par défaut
ALTER TABLE `shipments` 
ADD COLUMN `transport_mode` VARCHAR(20) NOT NULL DEFAULT 'land' 
AFTER `status`;

-- 2. Mise à jour de sécurité pour assurer que toutes les expéditions existantes
-- ont bien la valeur terrestre 'land' (cohérente avec le réseau actuel)
UPDATE `shipments` 
SET `transport_mode` = 'land' 
WHERE `transport_mode` IS NULL OR `transport_mode` = '';

-- 3. Enregistrement dans schema_migrations si la table existe
INSERT INTO `schema_migrations` (`version`, `applied_at`) 
VALUES ('019_add_transport_mode', NOW())
ON DUPLICATE KEY UPDATE `applied_at` = NOW();

-- ============================================================================
-- DOWN: Procédure de retour arrière (Rollback réversible)
-- ============================================================================
-- En cas de besoin de rollback sur MySQL / MariaDB (SiteGround) :
--
-- ALTER TABLE `shipments` DROP COLUMN `transport_mode`;
-- DELETE FROM `schema_migrations` WHERE `version` = '019_add_transport_mode';
