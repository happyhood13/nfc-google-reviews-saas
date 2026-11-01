<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Configuración de Base de Datos (PDO)
 * ==============================================================================
 * Arquitectura Segura con Prepared Statements y manejo estricto de excepciones.
 */

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    // Parámetros de conexión predeterminados para XAMPP
    private const DB_HOST = 'localhost';
    private const DB_PORT = '3306';
    private const DB_NAME = 'saas_nfc_qr';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    /**
     * Patrón Singleton para obtener la conexión PDO reutilizable
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: self::DB_HOST;
            $port = getenv('DB_PORT') ?: self::DB_PORT;
            $db   = getenv('DB_NAME') ?: self::DB_NAME;
            $user = getenv('DB_USER') ?: self::DB_USER;
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : self::DB_PASS;

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=" . self::DB_CHARSET;

            $options = [
                // Forzar excepciones en cualquier error SQL (OWASP: Fail securely)
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Resultados como arreglos asociativos por defecto
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Deshabilitar emulación de consultas preparadas para máxima protección contra SQLi
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Conexiones persistentes optimizadas y UTF-8 completo
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Registrar error en log seguro sin exponer credenciales en pantalla
                error_log('[DB Error]: ' . $e->getMessage());

                // Si la base de datos no existe aún, brindar mensaje claro para XAMPP
                http_response_code(500);
                echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error de Base de Datos</title>
                <script src="https://cdn.tailwindcss.com"></script></head>
                <body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
                <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-red-100 text-center">
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">!</div>
                    <h1 class="text-xl font-bold text-slate-800 mb-2">Error de Conexión a MySQL</h1>
                    <p class="text-sm text-slate-600 mb-4">No fue posible conectar con la base de datos <strong>' . htmlspecialchars($db, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
                    <p class="text-xs text-slate-500 bg-slate-100 p-3 rounded text-left font-mono mb-4">Verifica que MySQL esté activo en el Panel de XAMPP y que hayas importado el script database/schema.sql.</p>
                    <a href="index.php" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Reintentar conexión</a>
                </div></body></html>';
                exit;
            }
        }

        return self::$instance;
    }
}
