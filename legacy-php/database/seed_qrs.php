<?php
require_once __DIR__ . '/../app/Services/QrCodeService.php';

$svc = new \App\Services\QrCodeService();

$r1 = $svc->generateForBusiness('https://maps.app.goo.gl/9DqU1m8xK7y2Z9pA6', 'cafe_roma');
copy(dirname(__DIR__) . '/' . $r1['svg_path'], dirname(__DIR__) . '/uploads/qrcodes/demo_cafe_roma.svg');
copy(dirname(__DIR__) . '/' . $r1['png_path'], dirname(__DIR__) . '/uploads/qrcodes/demo_cafe_roma.png');

$r2 = $svc->generateForBusiness('https://maps.app.goo.gl/7YzT4w1uL9v3B8rC2', 'dental_boutique');
copy(dirname(__DIR__) . '/' . $r2['svg_path'], dirname(__DIR__) . '/uploads/qrcodes/demo_dental_boutique.svg');
copy(dirname(__DIR__) . '/' . $r2['png_path'], dirname(__DIR__) . '/uploads/qrcodes/demo_dental_boutique.png');

echo "Demo QRs generated successfully.\n";
