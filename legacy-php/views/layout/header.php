<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Header Layout
 * ==============================================================================
 */
declare(strict_types=1);
use App\Config\Security;
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle ?? 'SaaS NFC & QR Reviews') ?></title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        google: {
                            blue: '#4285F4',
                            red: '#EA4335',
                            yellow: '#FBBC05',
                            green: '#34A853'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Estilos personalizados para animaciones y estética SaaS */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .gradient-border {
            position: relative;
            background: linear-gradient(60deg, #4f46e5, #06b6d4);
            padding: 1px;
            border-radius: 1rem;
        }
        /* Reglas de impresión para Tarjetas NFC y Expositores */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print, header, footer, .toast-container, nav, .action-bar {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .print-card-container {
                box-shadow: none !important;
                border: 1px dashed #ccc !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="flex flex-col min-h-full text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Navbar Principal -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <a href="index.php" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-cyan-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-wifi text-lg rotate-90"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-display font-bold text-xl text-slate-900 tracking-tight">Review<span class="text-brand-600">NFC</span></span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-brand-50 text-brand-700 border border-brand-200">PRO</span>
                            </div>
                            <p class="text-[11px] text-slate-500 hidden sm:block">Plataforma SaaS para Reseñas de Google</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="index.php" class="px-3.5 py-2 rounded-lg text-sm font-medium <?= empty($_GET['action']) ? 'text-brand-600 bg-brand-50/80 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                        <i class="fa-solid fa-chart-pie mr-1.5 text-xs"></i> Dashboard
                    </a>
                    <a href="index.php?action=create" class="px-3.5 py-2 rounded-lg text-sm font-medium <?= (isset($_GET['action']) && $_GET['action'] === 'create') ? 'text-brand-600 bg-brand-50/80 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                        <i class="fa-solid fa-plus mr-1.5 text-xs"></i> Nuevo Negocio
                    </a>
                </nav>

                <!-- Action CTA -->
                <div class="flex items-center gap-3">
                    <a href="index.php?action=create" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-md shadow-brand-600/20 hover:shadow-lg hover:shadow-brand-600/30 active:scale-95 transition-all">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Registrar Negocio</span>
                    </a>
                </div>

            </div>
        </div>
    </header>

    <!-- Contenedor de Alertas Flash -->
    <div class="toast-container fixed bottom-5 right-5 z-50 flex flex-col gap-3 max-w-md w-full px-4 pointer-events-none">
        <?php if (!empty($flashes)): ?>
            <?php foreach ($flashes as $flash): ?>
                <?php
                    $isSuccess = $flash['type'] === 'success';
                    $bgClass   = $isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800';
                    $iconClass = $isSuccess ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-rose-500';
                ?>
                <div class="pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-xl <?= $bgClass ?> transition-all transform duration-300 animate-slide-up" role="alert">
                    <i class="fa-solid <?= $iconClass ?> text-lg mt-0.5"></i>
                    <div class="flex-1 text-sm font-medium leading-relaxed">
                        <?= e($flash['message']) ?>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Contenido Principal -->
    <main class="flex-1 pb-16">
