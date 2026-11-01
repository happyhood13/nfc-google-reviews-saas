<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Vista: Dashboard Principal
 * ==============================================================================
 */
declare(strict_types=1);

use App\Config\Security;

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

    <!-- Encabezado del Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                Panel de Negocios & Tarjetas NFC
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Monitorea tus comercios asociados, genera tarjetas inteligentes y códigos QR para reseñas de Google Maps.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?action=create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-md shadow-brand-600/25 transition active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Nuevo Negocio</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas SaaS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Métrica: Total Negocios -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Negocios Registrados</span>
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-store"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-display font-bold text-slate-900"><?= (int)$stats['total_businesses'] ?></span>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                    Activos: <?= (int)$stats['active_businesses'] ?>
                </span>
            </div>
        </div>

        <!-- Métrica: Taps NFC & Escaneos QR -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Taps NFC & Escaneos</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-wifi rotate-90"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-display font-bold text-slate-900"><?= number_format((int)$stats['total_scans']) ?></span>
                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                    Interacciones
                </span>
            </div>
        </div>

        <!-- Métrica: Tasa de Conversión -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Promedio por Comercio</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <?php 
                    $avg = $stats['total_businesses'] > 0 ? round($stats['total_scans'] / $stats['total_businesses'], 1) : 0;
                ?>
                <span class="text-3xl font-display font-bold text-slate-900"><?= $avg ?></span>
                <span class="text-xs text-slate-400">taps / negocio</span>
            </div>
        </div>

        <!-- Métrica: Último Comercio -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Última Alta</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="truncate">
                <p class="text-base font-display font-bold text-slate-900 truncate" title="<?= e($stats['latest_business']) ?>">
                    <?= e($stats['latest_business']) ?>
                </p>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    <?= !empty($stats['latest_date']) ? date('d/m/Y H:i', strtotime($stats['latest_date'])) : 'Sin registros' ?>
                </p>
            </div>
        </div>

    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <form method="GET" action="index.php" class="flex-1 w-full flex items-center gap-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= e($search ?? '') ?>" 
                    placeholder="Buscar por nombre de negocio o teléfono..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                >
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs sm:text-sm font-medium transition">
                Buscar
            </button>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs sm:text-sm transition flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Limpiar
                </a>
            <?php endif; ?>
        </form>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end text-xs text-slate-500">
            <span>Total mostrados: <strong class="text-slate-800"><?= count($businesses) ?></strong></span>
        </div>

    </div>

    <!-- Tabla de Comercios Registrados -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <?php if (empty($businesses)): ?>
            <!-- Estado Vacío -->
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-store-slash"></i>
                </div>
                <h3 class="font-display font-bold text-slate-900 text-lg mb-1">No se encontraron negocios</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">
                    <?= !empty($search) ? 'No hay comercios que coincidan con tu búsqueda. Intenta con otros términos.' : 'Aún no has registrado ningún negocio. Crea el primero para generar su tarjeta NFC y código QR.' ?>
                </p>
                <a href="index.php?action=create" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-md shadow-brand-600/25 transition">
                    <i class="fa-solid fa-plus text-xs"></i> Registrar Primer Negocio
                </a>
            </div>
        <?php else: ?>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">Comercio</th>
                            <th class="py-3.5 px-4">Teléfono</th>
                            <th class="py-3.5 px-4">Reseñas Google Maps</th>
                            <th class="py-3.5 px-4 text-center">Código QR</th>
                            <th class="py-3.5 px-4 text-center">Taps NFC</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <?php foreach ($businesses as $b): ?>
                            <?php 
                                $cleanPhone = preg_replace('/[^0-9]/', '', $b['telefono']);
                                $initials = mb_strtoupper(mb_substr($b['nombre_negocio'], 0, 2, 'UTF-8'), 'UTF-8');
                                $redirectUrl = 'index.php?action=redirect&slug=' . urlencode($b['slug']);
                                $downloadSvgUrl = 'index.php?action=downloadQr&id=' . (int)$b['id'] . '&format=svg';
                                $downloadPngUrl = 'index.php?action=downloadQr&id=' . (int)$b['id'] . '&format=png';
                            ?>
                            <tr class="hover:bg-slate-50/60 transition group">
                                
                                <!-- Nombre del Negocio y Logotipo -->
                                <td class="py-4 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($b['logo_path']) && file_exists(dirname(__DIR__, 2) . '/' . $b['logo_path'])): ?>
                                            <img src="<?= e($b['logo_path']) ?>" alt="<?= e($b['nombre_negocio']) ?>" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm flex-shrink-0">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-sm">
                                                <?= e($initials) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <a href="index.php?action=show&id=<?= (int)$b['id'] ?>" class="font-display font-semibold text-slate-900 hover:text-brand-600 transition truncate block text-sm">
                                                <?= e($b['nombre_negocio']) ?>
                                            </a>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-mono bg-slate-100 text-slate-600">
                                                    /r/<?= e($b['slug']) ?>
                                                </span>
                                                <button type="button" onclick="copyToClipboard(window.location.origin + window.location.pathname.replace('index.php', '') + '<?= $redirectUrl ?>', '¡Enlace de tarjeta NFC copiado!')" class="text-slate-400 hover:text-brand-600 transition" title="Copiar enlace NFC directo">
                                                    <i class="fa-regular fa-copy text-[11px]"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Teléfono -->
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-700 font-medium"><?= e($b['telefono']) ?></span>
                                        <?php if (!empty($cleanPhone)): ?>
                                            <a href="https://wa.me/<?= e($cleanPhone) ?>" target="_blank" rel="noopener noreferrer" class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center text-xs transition" title="Abrir en WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Enlace de Reseñas de Google Maps -->
                                <td class="py-4 px-4 max-w-[200px] sm:max-w-xs">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-brands fa-google text-slate-400 text-xs flex-shrink-0"></i>
                                        <a href="<?= e($b['google_maps_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-brand-600 hover:text-brand-800 hover:underline truncate block" title="<?= e($b['google_maps_url']) ?>">
                                            <?= e($b['google_maps_url']) ?>
                                        </a>
                                        <button type="button" onclick="copyToClipboard('<?= addslashes(e($b['google_maps_url'])) ?>', '¡Enlace de Google Maps copiado!')" class="text-slate-400 hover:text-slate-700 flex-shrink-0" title="Copiar enlace">
                                            <i class="fa-regular fa-copy text-xs"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Miniatura Código QR Interactivo -->
                                <td class="py-4 px-4 text-center">
                                    <button 
                                        type="button" 
                                        onclick="openQrModal('<?= e($b['qr_code_path']) ?>', '<?= addslashes(e($b['nombre_negocio'])) ?>', window.location.origin + window.location.pathname.replace('index.php', '') + '<?= $redirectUrl ?>', '<?= $downloadSvgUrl ?>', '<?= $downloadPngUrl ?>')"
                                        class="p-1 rounded-lg border border-slate-200 bg-white hover:border-brand-500 hover:shadow-md transition group/qr inline-block"
                                        title="Click para ver QR en grande"
                                    >
                                        <img src="<?= e($b['qr_code_path']) ?>" alt="QR" class="w-10 h-10 object-contain rounded group-hover/qr:scale-105 transition-transform">
                                    </button>
                                </td>

                                <!-- Taps NFC / Visitas -->
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <i class="fa-solid fa-wifi text-[9px] rotate-90"></i>
                                        <?= (int)$b['total_scans'] ?>
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- Ver Ficha / Tarjeta -->
                                        <a href="index.php?action=show&id=<?= (int)$b['id'] ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 flex items-center gap-1.5 transition" title="Ver Tarjeta NFC & Plantilla">
                                            <i class="fa-solid fa-id-card text-xs"></i>
                                            <span>Ficha NFC</span>
                                        </a>

                                        <!-- Descarga Rápida PNG -->
                                        <a href="<?= $downloadPngUrl ?>" class="w-8 h-8 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center transition" title="Descargar QR en PNG (HD)">
                                            <i class="fa-solid fa-download text-xs"></i>
                                        </a>

                                        <!-- Eliminar Negocio -->
                                        <button type="button" onclick="confirmDelete(<?= (int)$b['id'] ?>, '<?= addslashes(e($b['nombre_negocio'])) ?>')" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition" title="Eliminar Negocio">
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
