<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Funciones de Ayuda Globales para Plantillas
 * ==============================================================================
 */

declare(strict_types=1);

use App\Config\Security;

if (!function_exists('e')) {
    /**
     * Escape contextual contra ataques Cross-Site Scripting (XSS)
     */
    function e(?string $str): string
    {
        return Security::e($str);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Renderiza un input oculto con el token CSRF actual
     */
    function csrf_field(): string
    {
        return Security::csrfField();
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retorna el token CSRF actual en texto plano
     */
    function csrf_token(): string
    {
        return Security::generateCsrfToken();
    }
}
