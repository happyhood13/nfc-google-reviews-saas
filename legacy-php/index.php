<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Front Controller & Enrutador Principal
 * ==============================================================================
 * Punto de entrada único de la aplicación. Aplica cabeceras de seguridad OWASP,
 * inicializa el contexto de sesión y enruta las solicitudes a los controladores.
 */

declare(strict_types=1);

// Reporte de errores controlado (Logs internos sin fugar excepciones críticas al cliente)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Carga de dependencias esenciales
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/lib/QRCode.php';
require_once __DIR__ . '/app/Services/QrCodeService.php';
require_once __DIR__ . '/app/Models/BusinessModel.php';
require_once __DIR__ . '/app/Controllers/BusinessController.php';

use App\Config\Security;
use App\Controllers\BusinessController;

// 1. Inicializar sesión segura con directivas HttpOnly y SameSite=Lax
Security::initSession();

// 2. Enviar cabeceras de seguridad estrictas (CSP, X-Frame-Options, X-Content-Type-Options)
Security::sendSecurityHeaders();

// 3. Capturar acción solicitada
$action = isset($_GET['action']) ? Security::sanitizeString((string)$_GET['action'], 40) : 'index';

// 4. Instanciar controlador
$controller = new BusinessController();

// 5. Enrutamiento controlado
switch ($action) {
    case 'index':
        $controller->index();
        break;

    case 'create':
        $controller->create();
        break;

    case 'store':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: index.php?action=create');
            exit;
        }
        $controller->store();
        break;

    case 'show':
        $controller->show();
        break;

    case 'redirect':
        $controller->redirect();
        break;

    case 'downloadQr':
        $controller->downloadQr();
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: index.php');
            exit;
        }
        $controller->delete();
        break;

    default:
        http_response_code(404);
        $pageTitle = 'Página no encontrada (404)';
        require_once __DIR__ . '/views/layout/header.php';
        echo '<div class="max-w-md mx-auto my-20 p-8 bg-white rounded-2xl border border-slate-200 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">404</div>
            <h1 class="font-display font-bold text-xl text-slate-900 mb-2">Página no encontrada</h1>
            <p class="text-xs text-slate-500 mb-6">La ruta a la que intentas acceder no existe en el sistema.</p>
            <a href="index.php" class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 text-white hover:bg-brand-500 transition">Volver al Dashboard</a>
        </div>';
        require_once __DIR__ . '/views/layout/footer.php';
        break;
}
