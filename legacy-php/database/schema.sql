-- ==============================================================================
-- SaaS Local: Plataforma de Tarjetas NFC y Códigos QR para Reseñas de Google Maps
-- Base de Datos: saas_nfc_qr
-- Motor: InnoDB | Cotejamiento: utf8mb4_unicode_ci (Soporte completo para emojis e internacionalización)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `saas_nfc_qr` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE `saas_nfc_qr`;

-- Tabla de Negocios / Clientes
CREATE TABLE IF NOT EXISTS `negocios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre_negocio` VARCHAR(150) NOT NULL,
    `telefono` VARCHAR(30) NOT NULL,
    `logo_path` VARCHAR(255) NULL DEFAULT NULL,
    `google_maps_url` TEXT NOT NULL,
    `qr_code_path` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(80) NOT NULL UNIQUE,
    `total_scans` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de prueba iniciales (Semillas de demostración)
INSERT INTO `negocios` (`nombre_negocio`, `telefono`, `logo_path`, `google_maps_url`, `qr_code_path`, `slug`, `total_scans`, `status`) 
VALUES 
(
    'Café & Bistro La Roma', 
    '+52 55 1234 5678', 
    NULL, 
    'https://maps.app.goo.gl/9DqU1m8xK7y2Z9pA6', 
    'uploads/qrcodes/demo_cafe_roma.svg', 
    'cafe-roma', 
    42, 
    'activo'
),
(
    'Dental Boutique Pro', 
    '+52 55 9876 5432', 
    NULL, 
    'https://maps.app.goo.gl/7YzT4w1uL9v3B8rC2', 
    'uploads/qrcodes/demo_dental_boutique.svg', 
    'dental-boutique', 
    89, 
    'activo'
)
ON DUPLICATE KEY UPDATE `nombre_negocio` = VALUES(`nombre_negocio`);
