<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Controlador de Negocios
 * ==============================================================================
 * Implementa la lógica de negocio, validaciones estrictas OWASP,
 * procesamiento seguro de archivos multipart y generación de códigos QR.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Security;
use App\Models\BusinessModel;
use App\Services\QrCodeService;

class BusinessController
{
    private BusinessModel $businessModel;
    private QrCodeService $qrService;

    public function __construct()
    {
        $this->businessModel = new BusinessModel();
        $this->qrService = new QrCodeService();
    }

    /**
     * Vista principal: Dashboard con estadísticas y tabla interactiva de comercios
     */
    public function index(): void
    {
        $search = isset($_GET['search']) ? Security::sanitizeString((string)$_GET['search'], 100) : null;
        $status = isset($_GET['status']) ? Security::sanitizeString((string)$_GET['status'], 20) : null;

        $businesses = $this->businessModel->getAll($search, $status);
        $stats = $this->businessModel->getStats();
        $flashes = Security::getFlashes();

        $pageTitle = 'Dashboard de Negocios & Tarjetas NFC';
        require_once dirname(__DIR__, 2) . '/views/dashboard.php';
    }

    /**
     * Muestra el formulario de registro de un nuevo comercio
     */
    public function create(): void
    {
        $flashes = Security::getFlashes();
        $csrfToken = Security::generateCsrfToken();
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $pageTitle = 'Registrar Nuevo Negocio | NFC & QR Reviews';
        require_once dirname(__DIR__, 2) . '/views/create.php';
    }

    /**
     * Procesa la creación de un nuevo comercio con validación OWASP estricta
     */
    public function store(): void
    {
        // 1. Verificación obligatoria de Token CSRF
        $token = $_POST['csrf_token'] ?? null;
        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            Security::setFlash('error', 'Error de seguridad: Solicitud o Token CSRF inválido.');
            header('Location: index.php?action=create');
            exit;
        }

        // 2. Sanitización y validación de campos de texto
        $nombreNegocio  = Security::sanitizeString($_POST['nombre_negocio'] ?? '', 150);
        $telefono       = Security::sanitizeString($_POST['telefono'] ?? '', 30);
        $googleMapsUrl  = trim($_POST['google_maps_url'] ?? '');

        // Guardar old input para conveniencia del usuario en caso de error
        $_SESSION['old_input'] = [
            'nombre_negocio'  => $nombreNegocio,
            'telefono'        => $telefono,
            'google_maps_url' => $googleMapsUrl
        ];

        $errors = [];

        // Validación de Nombre del Negocio
        if (mb_strlen($nombreNegocio, 'UTF-8') < 2) {
            $errors[] = 'El nombre del negocio debe tener al menos 2 caracteres.';
        }

        // Validación estricta de Teléfono
        if (!Security::validatePhone($telefono)) {
            $errors[] = 'El teléfono proporcionado no tiene un formato válido (ej: +52 55 1234 5678).';
        }

        // Validación estricta de Enlace de Google Maps (OWASP Top 10 - SSRF & Open Redirect Mitigation)
        if (!Security::validateGoogleMapsUrl($googleMapsUrl)) {
            $errors[] = 'El enlace de Google Maps no es válido. Debe ser un enlace oficial de Google Maps o de Reseñas de Google (ej: https://maps.app.goo.gl/... o https://goo.gl/maps/...).';
        }

        // 3. Procesamiento y validación segura de Subida de Archivos (Logotipo)
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['logo'];

            // Verificar si hubo error nativo en la subida
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Ocurrió un error al subir el logotipo (Código de error: ' . (int)$file['error'] . ').';
            } else {
                // Límite de tamaño: Máximo 2.5 MB (2,621,440 bytes)
                $maxBytes = 2.5 * 1024 * 1024;
                if ($file['size'] > $maxBytes) {
                    $errors[] = 'El archivo de logotipo excede el tamaño máximo permitido de 2.5 MB.';
                }

                // Extensión en lista blanca
                $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($originalExt, $allowedExtensions, true)) {
                    $errors[] = 'Formato de imagen no permitido. Solo se aceptan extensiones: .jpg, .jpeg, .png y .webp.';
                }

                // Verificación de Tipo MIME Real mediante Fileinfo (previene extensiones falsas / polyglots)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mimeType, $allowedMimes, true)) {
                    $errors[] = 'El contenido del archivo no corresponde a una imagen válida.';
                }

                // Verificación adicional de dimensiones de imagen con getimagesize
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $errors[] = 'El archivo subido está corrupto o no es una imagen válida.';
                }

                // Si no hay errores, guardar con nombre criptográfico aleatorio
                if (empty($errors)) {
                    $logosDir = dirname(__DIR__, 2) . '/uploads/logos';
                    if (!is_dir($logosDir)) {
                        mkdir($logosDir, 0755, true);
                    }

                    $safeFileName = 'logo_' . bin2hex(random_bytes(12)) . '.' . $originalExt;
                    $destination = $logosDir . '/' . $safeFileName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $logoPath = 'uploads/logos/' . $safeFileName;
                    } else {
                        $errors[] = 'No se pudo guardar el logotipo en el servidor.';
                    }
                }
            }
        }

        // Si existen errores de validación, abortar y retornar con feedback
        if (!empty($errors)) {
            foreach ($errors as $err) {
                Security::setFlash('error', $err);
            }
            header('Location: index.php?action=create');
            exit;
        }

        // 4. Generación de Slug único para el comercio
        $baseSlug = Security::generateSlug($nombreNegocio);
        $slug = $baseSlug;
        $counter = 1;
        while ($this->businessModel->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // 5. Determinar la URL destino para la Tarjeta NFC y Código QR
        // En una arquitectura SaaS profesional, el QR y el chip NFC apuntan a la URL de redirección
        // de la plataforma (ej: /r/{slug} o index.php?action=redirect&slug={slug}).
        // Esto permite:
        // A) Contabilizar escaneos y taps NFC en tiempo real.
        // B) Si el cliente cambia de URL de reseñas en Google en el futuro, no pierde las tarjetas impresas.
        $baseUrl = $this->getBaseUrl();
        $nfcRedirectUrl = $baseUrl . '/index.php?action=redirect&slug=' . urlencode($slug);

        // 6. Generar Códigos QR (SVG Vectorial para imprenta y PNG HD)
        $qrResults = $this->qrService->generateForBusiness($nfcRedirectUrl, $slug);

        // 7. Insertar en la Base de Datos usando Prepared Statements
        $businessId = $this->businessModel->create([
            'nombre_negocio'  => $nombreNegocio,
            'telefono'        => $telefono,
            'logo_path'       => $logoPath,
            'google_maps_url' => $googleMapsUrl,
            'qr_code_path'    => $qrResults['svg_path'],
            'slug'            => $slug,
            'total_scans'     => 0,
            'status'          => 'activo'
        ]);

        unset($_SESSION['old_input']);
        Security::setFlash('success', '¡Negocio "' . $nombreNegocio . '" registrado con éxito! Tu tarjeta NFC y código QR están listos para usar.');

        // Redirigir a la vista de detalle
        header('Location: index.php?action=show&id=' . $businessId);
        exit;
    }

    /**
     * Muestra la vista de detalle con la Tarjeta NFC, QR, opciones de descarga e impresión
     */
    public function show(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $business = $this->businessModel->getById($id);

        if (!$business) {
            http_response_code(404);
            Security::setFlash('error', 'El comercio solicitado no existe o fue retirado.');
            header('Location: index.php');
            exit;
        }

        $baseUrl = $this->getBaseUrl();
        $nfcUrl = $baseUrl . '/index.php?action=redirect&slug=' . urlencode($business['slug']);

        $flashes = Security::getFlashes();
        $pageTitle = 'Ficha NFC y QR: ' . $business['nombre_negocio'];
        require_once dirname(__DIR__, 2) . '/views/detail.php';
    }

    /**
     * Endpoint público de redirección para Taps NFC y escaneos de QR
     * Incrementa el contador de visitas y redirige inmediatamente (302) a Google Maps
     */
    public function redirect(): void
    {
        $slug = isset($_GET['slug']) ? Security::sanitizeString((string)$_GET['slug'], 80) : '';

        if (empty($slug)) {
            http_response_code(400);
            echo 'Solicitud de redirección inválida.';
            exit;
        }

        $business = $this->businessModel->getBySlug($slug);

        if (!$business || $business['status'] !== 'activo') {
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Comercio No Encontrado</title>
            <script src="https://cdn.tailwindcss.com"></script></head>
            <body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4">
            <div class="text-center max-w-sm">
                <div class="w-16 h-16 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">✕</div>
                <h1 class="text-xl font-bold mb-2">Comercio no disponible</h1>
                <p class="text-sm text-slate-400">Esta tarjeta NFC o código QR no está activo o ha sido dado de baja.</p>
            </div></body></html>';
            exit;
        }

        // Registrar tap atómicamente
        $this->businessModel->incrementScans((int)$business['id']);

        // Redirección segura hacia Google Maps
        header('Location: ' . $business['google_maps_url'], true, 302);
        exit;
    }

    /**
     * Descarga segura de archivos QR generados (SVG o PNG)
     */
    public function downloadQr(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $format = isset($_GET['format']) && strtolower($_GET['format']) === 'png' ? 'png' : 'svg';

        $business = $this->businessModel->getById($id);
        if (!$business) {
            http_response_code(404);
            exit('Comercio no encontrado.');
        }

        $baseDir = dirname(__DIR__, 2);
        
        // Si piden PNG, buscamos el archivo con extensión .png reemplazando la ruta
        $relativePath = $business['qr_code_path'];
        if ($format === 'png') {
            $relativePath = preg_replace('/\.svg$/', '.png', $relativePath);
        }

        $fullPath = $baseDir . '/' . $relativePath;

        // Si el archivo PNG no existía aún en disco, generarlo al vuelo
        if (!file_exists($fullPath)) {
            $baseUrl = $this->getBaseUrl();
            $nfcRedirectUrl = $baseUrl . '/index.php?action=redirect&slug=' . urlencode($business['slug']);
            $this->qrService->generateForBusiness($nfcRedirectUrl, $business['slug']);
        }

        if (!file_exists($fullPath)) {
            http_response_code(404);
            exit('Archivo QR no encontrado.');
        }

        // Prevenir Directory Traversal validando que el archivo resida dentro de uploads/qrcodes
        $realPath = realpath($fullPath);
        $expectedDir = realpath($baseDir . '/uploads/qrcodes');
        if ($realPath === false || !str_starts_with($realPath, $expectedDir)) {
            http_response_code(403);
            exit('Acceso denegado.');
        }

        $safeName = 'qr_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $business['slug']) . '.' . $format;
        $contentType = ($format === 'png') ? 'image/png' : 'image/svg+xml';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Elimina un comercio y sus archivos físicos asociados
     */
    public function delete(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $token = $_POST['csrf_token'] ?? null;

        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            Security::setFlash('error', 'Token de seguridad inválido.');
            header('Location: index.php');
            exit;
        }

        $business = $this->businessModel->getById($id);
        if ($business) {
            $baseDir = dirname(__DIR__, 2);

            // Eliminar logo si existe
            if (!empty($business['logo_path'])) {
                $logoFile = $baseDir . '/' . $business['logo_path'];
                if (file_exists($logoFile) && is_file($logoFile)) {
                    @unlink($logoFile);
                }
            }

            // Eliminar archivos QR (SVG y PNG)
            if (!empty($business['qr_code_path'])) {
                $svgFile = $baseDir . '/' . $business['qr_code_path'];
                $pngFile = preg_replace('/\.svg$/', '.png', $svgFile);
                if (file_exists($svgFile) && is_file($svgFile)) {
                    @unlink($svgFile);
                }
                if (file_exists($pngFile) && is_file($pngFile)) {
                    @unlink($pngFile);
                }
            }

            $this->businessModel->delete($id);
            Security::setFlash('success', 'El negocio "' . $business['nombre_negocio'] . '" ha sido eliminado.');
        }

        header('Location: index.php');
        exit;
    }

    /**
     * Calcula la URL base dinámica de la aplicación
     */
    private function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/qr/index.php';
        $dir = dirname($scriptName);
        $dir = ($dir === '/' || $dir === '\\') ? '' : rtrim(str_replace('\\', '/', $dir), '/');

        return "{$protocol}://{$host}{$dir}";
    }
}
