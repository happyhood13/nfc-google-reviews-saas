<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Vista: Detalle, Tarjeta NFC & Expositor para Impresión
 * ==============================================================================
 */
declare(strict_types=1);

use App\Config\Security;

require_once __DIR__ . '/layout/header.php';

$cleanPhone = preg_replace('/[^0-9]/', '', $business['telefono']);
$initials = mb_strtoupper(mb_substr($business['nombre_negocio'], 0, 2, 'UTF-8'), 'UTF-8');
$downloadSvgUrl = 'index.php?action=downloadQr&id=' . (int)$business['id'] . '&format=svg';
$downloadPngUrl = 'index.php?action=downloadQr&id=' . (int)$business['id'] . '&format=png';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">

    <!-- Barra Superior de Navegación y Acciones (Oculta en Impresión) -->
    <div class="no-print mb-8">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-4">
            <a href="index.php" class="hover:text-slate-800 transition">Dashboard</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-slate-800 font-semibold truncate max-w-xs"><?= e($business['nombre_negocio']) ?></span>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            
            <div class="flex items-center gap-4">
                <?php if (!empty($business['logo_path']) && file_exists(dirname(__DIR__, 2) . '/' . $business['logo_path'])): ?>
                    <img src="<?= e($business['logo_path']) ?>" alt="<?= e($business['nombre_negocio']) ?>" class="w-14 h-14 rounded-2xl object-cover border border-slate-200 shadow-sm flex-shrink-0">
                <?php else: ?>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center text-base flex-shrink-0 shadow-sm">
                        <?= e($initials) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="font-display font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
                            <?= e($business['nombre_negocio']) ?>
                        </h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Activo
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                        <span><i class="fa-solid fa-phone mr-1 text-slate-400"></i> <?= e($business['telefono']) ?></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-calendar mr-1 text-slate-400"></i> Creado: <?= date('d/m/Y', strtotime($business['created_at'])) ?></span>
                        <span>•</span>
                        <span class="text-indigo-600 font-medium"><i class="fa-solid fa-wifi rotate-90 mr-1"></i> <?= (int)$business['total_scans'] ?> Taps NFC</span>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción de Cabecera -->
            <div class="flex flex-wrap items-center gap-2">
                <button 
                    type="button" 
                    onclick="window.print()" 
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 active:scale-95 transition flex items-center gap-2"
                >
                    <i class="fa-solid fa-print"></i>
                    <span>Imprimir Ficha</span>
                </button>

                <div class="relative inline-block text-left" id="downloadDropdown">
                    <button 
                        type="button" 
                        onclick="document.getElementById('downloadMenu').classList.toggle('hidden')" 
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 active:scale-95 transition flex items-center gap-2 shadow-sm"
                    >
                        <i class="fa-solid fa-download"></i>
                        <span>Descargar QR</span>
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                    <div id="downloadMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-xl border border-slate-100 py-1.5 z-20">
                        <a href="<?= $downloadSvgUrl ?>" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium">
                            <i class="fa-solid fa-file-code text-indigo-600"></i>
                            <span>SVG Vector (Imprenta)</span>
                        </a>
                        <a href="<?= $downloadPngUrl ?>" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium">
                            <i class="fa-solid fa-image text-emerald-600"></i>
                            <span>PNG HD (1000x1000px)</span>
                        </a>
                    </div>
                </div>

                <a 
                    href="<?= e($business['google_maps_url']) ?>" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 active:scale-95 transition flex items-center gap-2"
                >
                    <i class="fa-brands fa-google"></i>
                    <span>Abrir en Google</span>
                </a>
            </div>

        </div>

    </div>

    <!-- Pestañas de Vista / Selector de Formato Físico (Oculto en Impresión) -->
    <div class="no-print flex items-center justify-center gap-3 mb-8">
        <button 
            type="button" 
            id="tabCardBtn" 
            onclick="switchView('card')"
            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-brand-600 text-white shadow-md shadow-brand-600/20 transition flex items-center gap-2"
        >
            <i class="fa-solid fa-id-card"></i>
            <span>Tarjeta NFC (85 x 54 mm)</span>
        </button>
        <button 
            type="button" 
            id="tabStandBtn" 
            onclick="switchView('stand')"
            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition flex items-center gap-2"
        >
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Expositor de Mostrador (Acrílico A6)</span>
        </button>
    </div>

    <!-- SECCIÓN 1: VISTA TARJETA FÍSICA NFC (Frontal & Posterior) -->
    <div id="viewCard" class="space-y-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center justify-center">
            
            <!-- Cara Frontal de la Tarjeta NFC -->
            <div class="flex flex-col items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 no-print">Cara Frontal (Anverso)</span>
                
                <div class="print-card-container relative w-full max-w-[420px] aspect-[85.6/53.98] rounded-2xl p-6 text-white shadow-2xl flex flex-col justify-between bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 border border-slate-700/80 overflow-hidden">
                    
                    <!-- Brillo de fondo sutil -->
                    <div class="absolute -top-20 -right-20 w-44 h-44 rounded-full bg-brand-500/15 blur-2xl pointer-events-none"></div>

                    <!-- Header Anverso -->
                    <div class="relative z-10 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($business['logo_path']) && file_exists(dirname(__DIR__, 2) . '/' . $business['logo_path'])): ?>
                                <img src="<?= e($business['logo_path']) ?>" alt="<?= e($business['nombre_negocio']) ?>" class="w-11 h-11 rounded-xl object-contain bg-white/10 backdrop-blur-md p-1 border border-white/20 shadow-sm">
                            <?php else: ?>
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                    <?= e($initials) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h3 class="font-display font-bold text-base text-white tracking-tight leading-tight truncate max-w-[200px]">
                                    <?= e($business['nombre_negocio']) ?>
                                </h3>
                                <p class="text-[10px] text-slate-400">Reseñas en Google</p>
                            </div>
                        </div>

                        <!-- Símbolo Contactless / NFC -->
                        <div class="flex items-center text-white/90">
                            <i class="fa-solid fa-wifi rotate-90 text-lg"></i>
                        </div>
                    </div>

                    <!-- Centro Anverso: 5 Estrellas Google -->
                    <div class="relative z-10 my-auto text-center py-2">
                        <div class="flex items-center justify-center gap-1.5 text-amber-400 text-lg mb-1.5 drop-shadow">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-200 tracking-wide uppercase">
                            ¡Califícanos con 5 Estrellas!
                        </p>
                    </div>

                    <!-- Footer Anverso -->
                    <div class="relative z-10 flex items-center justify-between pt-2.5 border-t border-white/10 text-[10px] text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-mobile-screen-button text-brand-400"></i> Acerca tu teléfono aquí
                        </span>
                        <span class="flex items-center gap-1.5 font-medium text-slate-300">
                            <i class="fa-brands fa-google text-white"></i> Reseñas
                        </span>
                    </div>

                </div>
            </div>

            <!-- Cara Posterior de la Tarjeta NFC (Con Código QR de Respaldo) -->
            <div class="flex flex-col items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 no-print">Cara Posterior (Reverso con QR)</span>
                
                <div class="print-card-container relative w-full max-w-[420px] aspect-[85.6/53.98] rounded-2xl p-6 text-slate-800 shadow-2xl flex items-center justify-between gap-4 bg-white border border-slate-200 overflow-hidden">
                    
                    <div class="flex-1 flex flex-col justify-between h-full py-1">
                        <div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="font-display font-bold text-xs text-slate-900 truncate block max-w-[170px]">
                                    <?= e($business['nombre_negocio']) ?>
                                </span>
                            </div>
                            <p class="text-[11px] font-semibold text-brand-600 leading-snug">
                                ¿Tu teléfono no tiene NFC?
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">
                                Abre la cámara de tu smartphone y enfoca este código QR para abrir el formulario de reseñas de Google Maps.
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-400">
                            <span>Tel: <?= e($business['telefono']) ?></span>
                            <span class="font-mono text-[9px]">/r/<?= e($business['slug']) ?></span>
                        </div>
                    </div>

                    <!-- QR Code Físico Posterior -->
                    <div class="w-28 h-28 p-1 bg-white border border-slate-200 rounded-xl shadow-sm flex items-center justify-center flex-shrink-0">
                        <img src="<?= e($business['qr_code_path']) ?>" alt="Código QR" class="w-full h-full object-contain rounded-lg">
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- SECCIÓN 2: EXPOSITOR DE MOSTRADOR / ACRÍLICO A6 (Ideal para imprimir y poner en mesa) -->
    <div id="viewStand" class="hidden flex flex-col items-center justify-center">
        
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 no-print">
            Plantilla Expositor de Mesa / Acrílico A6 (105 x 148 mm)
        </span>

        <!-- Contenedor Imprimible del Expositor -->
        <div class="print-card-container bg-white border border-slate-200 rounded-3xl p-8 sm:p-10 shadow-2xl max-w-md w-full text-center relative overflow-hidden">
            
            <!-- Barra Superior Decorativa de Google -->
            <div class="flex items-center justify-center gap-2 mb-6">
                <span class="w-3 h-3 rounded-full bg-google-blue"></span>
                <span class="w-3 h-3 rounded-full bg-google-red"></span>
                <span class="w-3 h-3 rounded-full bg-google-yellow"></span>
                <span class="w-3 h-3 rounded-full bg-google-green"></span>
            </div>

            <!-- Logotipo del Comercio -->
            <?php if (!empty($business['logo_path']) && file_exists(dirname(__DIR__, 2) . '/' . $business['logo_path'])): ?>
                <img src="<?= e($business['logo_path']) ?>" alt="<?= e($business['nombre_negocio']) ?>" class="w-20 h-20 rounded-2xl object-contain mx-auto mb-3 p-1.5 border border-slate-200 shadow-sm bg-slate-50">
            <?php else: ?>
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center text-xl mx-auto mb-3 shadow-md">
                    <?= e($initials) ?>
                </div>
            <?php endif; ?>

            <!-- Nombre del Negocio -->
            <h2 class="font-display font-extrabold text-2xl text-slate-900 mb-1">
                <?= e($business['nombre_negocio']) ?>
            </h2>

            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
                ¡Tu opinión nos importa mucho!
            </p>

            <!-- 5 Estrellas Google -->
            <div class="flex items-center justify-center gap-2 text-amber-400 text-2xl mb-6">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>

            <!-- Código QR Gigante Vectorial -->
            <div class="inline-block p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl shadow-inner mb-6">
                <img src="<?= e($business['qr_code_path']) ?>" alt="QR Reseñas" class="w-56 h-56 object-contain rounded-xl bg-white p-2">
            </div>

            <!-- 3 Pasos de Instrucción para Clientes -->
            <div class="grid grid-cols-3 gap-3 text-center pt-4 border-t border-slate-100">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-xs font-bold mb-1.5">
                        1
                    </div>
                    <p class="text-[11px] font-semibold text-slate-700">Abre tu cámara</p>
                    <p class="text-[10px] text-slate-400">o acerca tu móvil</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold mb-1.5">
                        2
                    </div>
                    <p class="text-[11px] font-semibold text-slate-700">Escanea el QR</p>
                    <p class="text-[10px] text-slate-400">o toca la pantalla</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold mb-1.5">
                        3
                    </div>
                    <p class="text-[11px] font-semibold text-slate-700">Deja 5 estrellas</p>
                    <p class="text-[10px] text-slate-400">¡Muchas gracias!</p>
                </div>
            </div>

        </div>

    </div>

    <!-- SECCIÓN INFORMATIVA: Datos de Configuración Técnica NFC (Oculta en Impresión) -->
    <div class="no-print mt-12 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <div>
                <h3 class="font-display font-bold text-base text-slate-900">
                    Especificaciones Técnicas & Programación NFC
                </h3>
                <p class="text-xs text-slate-500">
                    Utiliza estos datos para programar tus etiquetas o tarjetas NTAG213/NTAG215 mediante aplicaciones gratuitas como NFC Tools.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-code text-[11px]"></i> NDEF URI Compatible
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
            
            <!-- Enlace NFC de Redirección Puente -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/70">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="font-bold text-slate-700">Enlace a Grabar en el Chip NFC (Recomendado):</label>
                    <button 
                        type="button" 
                        onclick="copyToClipboard('<?= e($nfcUrl) ?>', '¡Enlace NFC copiado para grabador de chips!')" 
                        class="text-brand-600 hover:text-brand-800 font-semibold flex items-center gap-1 transition"
                    >
                        <i class="fa-regular fa-copy"></i> Copiar
                    </button>
                </div>
                <code class="block font-mono bg-white p-2.5 rounded-lg border border-slate-200 text-slate-800 text-[11px] truncate select-all">
                    <?= e($nfcUrl) ?>
                </code>
                <p class="text-[10px] text-slate-400 mt-1.5">
                    Permite contabilizar visitas en tiempo real y cambiar el destino sin necesidad de reescribir físicamente el chip NFC.
                </p>
            </div>

            <!-- Enlace Directo Oficial de Google Maps -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/70">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="font-bold text-slate-700">Destino Final (Google Maps Reviews):</label>
                    <button 
                        type="button" 
                        onclick="copyToClipboard('<?= addslashes(e($business['google_maps_url'])) ?>', '¡Enlace de Google Maps copiado!')" 
                        class="text-brand-600 hover:text-brand-800 font-semibold flex items-center gap-1 transition"
                    >
                        <i class="fa-regular fa-copy"></i> Copiar
                    </button>
                </div>
                <code class="block font-mono bg-white p-2.5 rounded-lg border border-slate-200 text-slate-800 text-[11px] truncate select-all">
                    <?= e($business['google_maps_url']) ?>
                </code>
                <p class="text-[10px] text-slate-400 mt-1.5">
                    Enlace verificado y protegido contra inyecciones y redirecciones abiertas (OWASP Top 10).
                </p>
            </div>

        </div>

    </div>

</div>

<!-- Scripts Vanilla JS para cambio de vista y dropdowns -->
<script>
    function switchView(mode) {
        const viewCard = document.getElementById('viewCard');
        const viewStand = document.getElementById('viewStand');
        const tabCardBtn = document.getElementById('tabCardBtn');
        const tabStandBtn = document.getElementById('tabStandBtn');

        if (mode === 'card') {
            viewCard.classList.remove('hidden');
            viewStand.classList.add('hidden');

            tabCardBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-brand-600 text-white shadow-md shadow-brand-600/20 transition flex items-center gap-2';
            tabStandBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition flex items-center gap-2';
        } else {
            viewCard.classList.add('hidden');
            viewStand.classList.remove('hidden');

            tabStandBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-brand-600 text-white shadow-md shadow-brand-600/20 transition flex items-center gap-2';
            tabCardBtn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition flex items-center gap-2';
        }
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('downloadDropdown');
        const menu = document.getElementById('downloadMenu');
        if (dropdown && menu && !dropdown.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
