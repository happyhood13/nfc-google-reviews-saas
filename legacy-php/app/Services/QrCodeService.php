<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Servicio de Generación de Códigos QR
 * ==============================================================================
 * Genera códigos QR vectoriales (SVG) de resolución infinita para imprenta
 * y tarjetas NFC, así como versiones rasterizadas en alta definición (PNG).
 */

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../../lib/QRCode.php';

use splitbrain\phpQRCode\QRCode;

class ExtendedQRCode extends QRCode
{
    public function getMatrixData(): array
    {
        return $this->dispatch_encode($this->data, $this->options);
    }
}

class QrCodeService
{
    private string $uploadDir;

    public function __construct(?string $uploadDir = null)
    {
        $this->uploadDir = $uploadDir ?: dirname(__DIR__, 2) . '/uploads/qrcodes';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Genera y guarda archivos QR tanto en SVG como en PNG para un negocio
     * 
     * @param string $targetUrl URL a la que apuntará el QR (enlace directo o URL puente de redirección NFC)
     * @param string $slug Identificador único del cliente
     * @return array Rutas relativas del SVG y PNG generados
     */
    public function generateForBusiness(string $targetUrl, string $slug): array
    {
        $qr = new ExtendedQRCode($targetUrl, ['s' => 'qrm']); // Nivel de corrección M (15%) o Q (25%)
        $matrix = $qr->getMatrixData();

        $hash = substr(bin2hex(random_bytes(8)), 0, 8);
        $baseFilename = "qr_{$slug}_{$hash}";

        $svgRelativePath = "uploads/qrcodes/{$baseFilename}.svg";
        $pngRelativePath = "uploads/qrcodes/{$baseFilename}.png";

        $svgFullPath = dirname(__DIR__, 2) . '/' . $svgRelativePath;
        $pngFullPath = dirname(__DIR__, 2) . '/' . $pngRelativePath;

        // 1. Generar y guardar SVG vectorial optimizado con margen blanco (Quiet Zone)
        $svgContent = $this->buildSvg($matrix);
        file_put_contents($svgFullPath, $svgContent);

        // 2. Generar y guardar PNG en Alta Resolución (1000 x 1000 px) usando GD
        $this->buildPng($matrix, $pngFullPath, 1000);

        return [
            'svg_path'    => $svgRelativePath,
            'png_path'    => $pngRelativePath,
            'svg_content' => $svgContent
        ];
    }

    /**
     * Construye un SVG limpio con zona de silencio (Quiet Zone) y estilo profesional
     */
    private function buildSvg(array $matrix): string
    {
        $grid = $matrix['b'];
        $matrixSize = count($grid);
        $margin = 3; // Módulos de margen de silencio
        $totalSize = $matrixSize + ($margin * 2);

        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" shape-rendering="crispEdges" width="100%%" height="100%%">' . "\n",
            $totalSize,
            $totalSize
        );
        
        // Fondo blanco
        $svg .= sprintf('  <rect width="%d" height="%d" fill="#FFFFFF"/>' . "\n", $totalSize, $totalSize);

        // Módulos del código QR
        $svg .= '  <g fill="#111827">' . "\n";
        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (!empty($grid[$y][$x])) {
                    $svg .= sprintf('    <rect x="%d" y="%d" width="1" height="1"/>' . "\n", $x + $margin, $y + $margin);
                }
            }
        }
        $svg .= '  </g>' . "\n";
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Genera un archivo PNG de alta resolución usando PHP GD
     */
    private function buildPng(array $matrix, string $outputPath, int $targetPixelSize = 1000): void
    {
        $grid = $matrix['b'];
        $matrixSize = count($grid);
        $margin = 3;
        $totalModules = $matrixSize + ($margin * 2);

        // Calcular escala por módulo
        $modulePixelSize = (int) max(1, floor($targetPixelSize / $totalModules));
        $actualImageSize = $totalModules * $modulePixelSize;

        $image = imagecreatetruecolor($actualImageSize, $actualImageSize);
        if (!$image) {
            return;
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 17, 24, 39); // Slate-900 moderno

        // Rellenar fondo
        imagefilledrectangle($image, 0, 0, $actualImageSize - 1, $actualImageSize - 1, $white);

        // Dibujar módulos
        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (!empty($grid[$y][$x])) {
                    $x1 = ($x + $margin) * $modulePixelSize;
                    $y1 = ($y + $margin) * $modulePixelSize;
                    $x2 = $x1 + $modulePixelSize - 1;
                    $y2 = $y1 + $modulePixelSize - 1;
                    imagefilledrectangle($image, $x1, $y1, $x2, $y2, $black);
                }
            }
        }

        imagepng($image, $outputPath, 9);
        imagedestroy($image);
    }
}
