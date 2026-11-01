<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Módulo Central de Seguridad y Sanitización (OWASP)
 * ==============================================================================
 * Prevención estricta contra:
 * - CSRF (Cross-Site Request Forgery) mediante tokens criptográficos
 * - XSS (Cross-Site Scripting) mediante escape contextual UTF-8
 * - Inyección de Cabeceras / Clickjacking mediante Security Headers
 * - Validación y Whitelisting de URLs de Google Maps
 */

declare(strict_types=1);

namespace App\Config;

class Security
{
    /**
     * Inicializa la sesión con directivas de cookies seguras
     */
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            session_set_cookie_params([
                'lifetime' => 86400, // 24 horas
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,      // Previene acceso a cookies desde JavaScript (Mitiga XSS)
                'samesite' => 'Lax'      // Mitiga CSRF en solicitudes cruzadas
            ]);

            session_start();
        }
    }

    /**
     * Envía cabeceras HTTP de seguridad para mitigar ataques web comunes
     */
    public static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Mitiga Clickjacking impidiendo que la app sea incrustada en iframes externos
        header('X-Frame-Options: SAMEORIGIN');
        
        // Previene la adivinanza de tipos MIME en navegadores (MIME Sniffing)
        header('X-Content-Type-Options: nosniff');
        
        // Habilita filtro XSS en navegadores antiguos
        header('X-XSS-Protection: 1; mode=block');
        
        // Control de fuga de información de origen en enlaces
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Política de seguridad de contenidos (CSP) compatible con Tailwind CDN y FontAwesome
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; " .
               "img-src 'self' data: blob: https:; " .
               "connect-src 'self'; " .
               "frame-ancestors 'self';";

        header("Content-Security-Policy: {$csp}");
    }

    /**
     * Genera un token CSRF criptográficamente seguro y lo asocia a la sesión
     */
    public static function generateCsrfToken(): string
    {
        self::initSession();

        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time'] > 7200)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Renderiza un campo HTML input oculto con el token CSRF
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Valida el token CSRF enviado vía POST usando hash_equals para evitar ataques de temporización
     */
    public static function validateCsrfToken(?string $token): bool
    {
        self::initSession();

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Escape seguro contra Cross-Site Scripting (XSS)
     */
    public static function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitiza cadenas de texto generales
     */
    public static function sanitizeString(string $input, int $maxLength = 255): string
    {
        $clean = strip_tags($input);
        $clean = trim($clean);
        return mb_substr($clean, 0, $maxLength, 'UTF-8');
    }

    /**
     * Valida números de teléfono (formato internacional o local con dígitos, espacios, guiones y +)
     */
    public static function validatePhone(string $phone): bool
    {
        $clean = trim($phone);
        // Longitud mínima 7 y máxima 25, caracteres permitidos +, números, espacios, paréntesis, guiones
        return (bool) preg_match('/^\+?[0-9\s\-\(\)\.]{7,25}$/', $clean);
    }

    /**
     * Valida que una URL pertenezca de manera estricta al ecosistema oficial de Google Maps
     * Acepta enlaces cortos (maps.app.goo.gl, goo.gl/maps) y enlaces largos estándar de Google Maps
     */
    public static function validateGoogleMapsUrl(string $url): bool
    {
        $url = trim($url);

        // 1. Debe ser una URL sintácticamente válida
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // 2. Solo protocolos http y https permitidos
        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';

        // 3. Dominios oficiales permitidos para Google Maps y Reseñas
        // Enlaces de compartir directo de la app móvil: maps.app.goo.gl
        if ($host === 'maps.app.goo.gl') {
            return true;
        }

        // Enlaces clásicos cortos: goo.gl/maps/...
        if ($host === 'goo.gl' && str_starts_with($path, '/maps')) {
            return true;
        }

        // Enlace oficial de reseña directa de Google Local: search.google.com/local/writereview?...
        if ($host === 'search.google.com' && (str_starts_with($path, '/local/writereview') || str_starts_with($path, '/local/reviews'))) {
            return true;
        }

        // Subdominio directo: maps.google.com o maps.google.com.mx etc.
        if (preg_match('/^maps\.google\.(com(\.[a-z]{2})?|[a-z]{2})$/', $host)) {
            return true;
        }

        // Dominio principal de google con ruta /maps
        if (preg_match('/^(www\.)?google\.(com(\.[a-z]{2})?|[a-z]{2})$/', $host) && str_starts_with($path, '/maps')) {
            return true;
        }

        // Enlace de Google Business / Place Profile
        if (preg_match('/^(www\.)?google\.(com(\.[a-z]{2})?|[a-z]{2})$/', $host) && (str_starts_with($path, '/search') || str_starts_with($path, '/business'))) {
            return true;
        }

        return false;
    }

    /**
     * Generador de slug seguro a partir del nombre del negocio
     */
    public static function generateSlug(string $text): string
    {
        // Reemplazar caracteres especiales y tildes
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
        // Reemplazar cualquier caracter no alfanumérico por guión
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim((string)$text, '-');
        
        return $text !== '' ? substr($text, 0, 60) : 'negocio-' . bin2hex(random_bytes(4));
    }

    /**
     * Manejo de mensajes flash para retroalimentación al usuario
     */
    public static function setFlash(string $type, string $message): void
    {
        self::initSession();
        $_SESSION['flash_messages'][] = [
            'type'    => $type, // 'success', 'error', 'info', 'warning'
            'message' => $message
        ];
    }

    public static function getFlashes(): array
    {
        self::initSession();
        $flashes = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $flashes;
    }
}


