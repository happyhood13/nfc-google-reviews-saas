<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Vista: Registro de Nuevo Negocio
 * ==============================================================================
 */
declare(strict_types=1);

use App\Config\Security;

require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
        <a href="index.php" class="hover:text-slate-800 transition">Dashboard</a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-800 font-semibold">Registrar Negocio</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Columna Izquierda: Formulario de Registro Seguro (7 cols) -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm">
            
            <div class="mb-6 pb-5 border-b border-slate-100">
                <h1 class="font-display font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
                    Registrar Nuevo Comercio
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Ingresa los datos para generar automáticamente el código QR vectorial y el perfil de la tarjeta inteligente NFC.
                </p>
            </div>

            <form action="index.php?action=store" method="POST" enctype="multipart/form-data" id="businessForm" class="space-y-5">
                
                <!-- Token CSRF Estricto (OWASP A01) -->
                <?= csrf_field() ?>

                <!-- Campo: Nombre del Negocio -->
                <div>
                    <label for="nombre_negocio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nombre del Negocio <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-store absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="text" 
                            name="nombre_negocio" 
                            id="nombre_negocio" 
                            required
                            maxlength="150"
                            value="<?= e($oldInput['nombre_negocio'] ?? '') ?>"
                            placeholder="Ej: Café & Panadería Artesanal" 
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                        >
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Este nombre se imprimirá en el frontal de la tarjeta NFC y expositor de mesa.</p>
                </div>

                <!-- Campo: Teléfono de Contacto -->
                <div>
                    <label for="telefono" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Teléfono / WhatsApp <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="tel" 
                            name="telefono" 
                            id="telefono" 
                            required
                            maxlength="30"
                            value="<?= e($oldInput['telefono'] ?? '') ?>"
                            placeholder="Ej: +52 55 1234 5678" 
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                        >
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Incluye código de país (+52, +34, etc.) para habilitar contacto directo en WhatsApp.</p>
                </div>

                <!-- Campo: Subida Segura de Logotipo -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Logotipo del Comercio <span class="text-slate-400 font-normal">(Opcional)</span>
                    </label>
                    
                    <div 
                        id="dropZone" 
                        class="relative border-2 border-dashed border-slate-200 hover:border-brand-500/60 rounded-2xl p-5 text-center bg-slate-50 hover:bg-slate-50/80 transition cursor-pointer group"
                    >
                        <input 
                            type="file" 
                            name="logo" 
                            id="logoInput" 
                            accept=".jpg,.jpeg,.png,.webp"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        >
                        
                        <!-- Estado Sin Archivo -->
                        <div id="dropPrompt" class="flex flex-col items-center justify-center pointer-events-none">
                            <div class="w-12 h-12 rounded-full bg-brand-50 group-hover:bg-brand-100 text-brand-600 flex items-center justify-center text-lg mb-2 transition">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">Arrastra tu logo o haz clic para examinar</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Formatos permitidos: .PNG, .JPG, .WEBP (Máx. 2.5 MB)</p>
                        </div>

                        <!-- Estado con Archivo Seleccionado (Preview) -->
                        <div id="dropPreview" class="hidden flex items-center justify-between pointer-events-auto bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <img id="previewImg" src="" alt="Logo Preview" class="w-12 h-12 rounded-lg object-contain bg-slate-100 border p-1">
                                <div class="text-left">
                                    <p id="fileName" class="text-xs font-bold text-slate-800 truncate max-w-[200px]">logo.png</p>
                                    <p id="fileSize" class="text-[10px] text-slate-400">120 KB</p>
                                </div>
                            </div>
                            <button type="button" id="removeLogoBtn" class="text-rose-500 hover:text-rose-700 p-2 text-xs font-semibold hover:bg-rose-50 rounded-lg transition">
                                <i class="fa-solid fa-trash-can mr-1"></i> Quitar
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Campo: Enlace de Reseñas de Google Maps -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="google_maps_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Enlace de Reseñas de Google Maps <span class="text-rose-500">*</span>
                        </label>
                        <a 
                            href="#" 
                            id="testLinkBtn" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="hidden text-[11px] font-semibold text-brand-600 hover:text-brand-800 flex items-center gap-1 transition"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Probar enlace
                        </a>
                    </div>
                    
                    <div class="relative">
                        <i class="fa-brands fa-google absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="url" 
                            name="google_maps_url" 
                            id="google_maps_url" 
                            required
                            value="<?= e($oldInput['google_maps_url'] ?? '') ?>"
                            placeholder="https://maps.app.goo.gl/... o https://goo.gl/maps/..." 
                            class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                        >
                        <!-- Indicador de Validación en Vivo -->
                        <div id="urlValidationIcon" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300">
                            <i class="fa-solid fa-circle-question text-sm"></i>
                        </div>
                    </div>
                    <div id="urlFeedback" class="text-[11px] text-slate-400 mt-1">
                        Pega el enlace corto que genera Google Maps al pulsar en "Compartir perfil del negocio" o "Pedir reseñas".
                    </div>
                </div>

                <!-- Guía rápida para obtener el enlace -->
                <div class="bg-brand-50/60 rounded-xl p-3.5 border border-brand-100 flex items-start gap-3">
                    <div class="w-6 h-6 rounded-md bg-brand-600 text-white flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div class="text-[11px] text-slate-600 leading-relaxed">
                        <strong class="text-slate-800">¿Cómo obtener el enlace de reseñas en 3 pasos?</strong><br>
                        1. Abre tu perfil en Google Maps. <br>
                        2. Pulsa en <em>"Pedir reseñas"</em> o <em>"Compartir perfil"</em>. <br>
                        3. Copia el enlace oficial (ej: <code class="text-brand-700 bg-white px-1 py-0.5 rounded border">https://maps.app.goo.gl/...</code>) y pégalo arriba.
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a href="index.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition">
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        id="submitBtn" 
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-md shadow-brand-600/25 active:scale-95 transition flex items-center gap-2"
                    >
                        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                        <span>Generar Tarjeta & Código QR</span>
                    </button>
                </div>

            </form>

        </div>

        <!-- Columna Derecha: Vista Previa Dinámica en Vivo (5 cols) -->
        <div class="lg:col-span-5 sticky top-24 space-y-6">
            
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vista Previa en Vivo</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> NFC Ready
                    </span>
                </div>

                <!-- Mockup Físico de Tarjeta NFC (Proporción Tarjeta 85.6 x 53.98 mm) -->
                <div class="relative w-full aspect-[85/54] rounded-2xl p-5 text-white shadow-xl overflow-hidden flex flex-col justify-between bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 border border-slate-700/60">
                    
                    <!-- Brillo Metálico Sutil -->
                    <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>

                    <!-- Header de la Tarjeta -->
                    <div class="relative z-10 flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <div id="mockupLogoCircle" class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center overflow-hidden">
                                <i id="mockupDefaultIcon" class="fa-solid fa-store text-white/70 text-xs"></i>
                                <img id="mockupLogoImg" src="" alt="Logo" class="w-full h-full object-cover hidden">
                            </div>
                            <div>
                                <h3 id="mockupBusinessName" class="font-display font-bold text-sm text-white tracking-tight truncate max-w-[140px] sm:max-w-[170px]">
                                    Nombre del Negocio
                                </h3>
                                <p class="text-[10px] text-slate-400">Reseñas Verificadas</p>
                            </div>
                        </div>

                        <!-- Icono NFC Contactless -->
                        <div class="flex items-center gap-1 text-white/80">
                            <i class="fa-solid fa-wifi rotate-90 text-sm"></i>
                        </div>
                    </div>

                    <!-- Cuerpo de la Tarjeta: 5 Estrellas Google y Llamado -->
                    <div class="relative z-10 my-auto flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-1 text-amber-400 text-xs mb-1">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-300">¡Tu opinión nos ayuda a crecer!</span>
                        </div>

                        <!-- Miniatura Simulación QR -->
                        <div class="w-14 h-14 bg-white p-1 rounded-lg shadow-sm flex items-center justify-center flex-shrink-0">
                            <div class="w-full h-full bg-slate-900 rounded flex items-center justify-center text-white">
                                <i class="fa-solid fa-qrcode text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Footer de la Tarjeta -->
                    <div class="relative z-10 flex items-center justify-between pt-2 border-t border-white/10 text-[9px] text-slate-400">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-mobile-screen-button text-[10px] text-indigo-400"></i> Acerca tu móvil o escanea
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fa-brands fa-google text-slate-300"></i> Google Maps
                        </span>
                    </div>

                </div>

                <p class="text-center text-[11px] text-slate-400 mt-3">
                    Vista simulada de la tarjeta física de PVC o acrílico lista para grabar con chip NTAG213/NTAG215.
                </p>

            </div>

            <!-- Caja Informativa de Seguridad & Especificaciones -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
                <h4 class="font-display font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-500"></i> Garantía de Seguridad OWASP
                </h4>
                <ul class="text-xs text-slate-500 space-y-2">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[11px] mt-0.5"></i>
                        <span><strong>Validación Antifraude:</strong> Verificación estricta de nombres de dominio de Google Maps.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[11px] mt-0.5"></i>
                        <span><strong>Protección Multipart:</strong> Renombrado criptográfico y chequeo de firma binaria MIME de imágenes.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[11px] mt-0.5"></i>
                        <span><strong>Anti-CSRF:</strong> Token único emitido por sesión validado estrictamente en servidor.</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>

</div>

<!-- Script Vanilla JS para la interactividad del formulario y preview en vivo -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('nombre_negocio');
        const urlInput = document.getElementById('google_maps_url');
        const testLinkBtn = document.getElementById('testLinkBtn');
        const urlIcon = document.getElementById('urlValidationIcon');
        const urlFeedback = document.getElementById('urlFeedback');
        
        const mockupName = document.getElementById('mockupBusinessName');
        const mockupLogoImg = document.getElementById('mockupLogoImg');
        const mockupDefaultIcon = document.getElementById('mockupDefaultIcon');

        const logoInput = document.getElementById('logoInput');
        const dropPrompt = document.getElementById('dropPrompt');
        const dropPreview = document.getElementById('dropPreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const removeLogoBtn = document.getElementById('removeLogoBtn');

        // 1. Actualización en tiempo real del Nombre en el Mockup
        nameInput.addEventListener('input', function() {
            const val = this.value.trim();
            mockupName.textContent = val !== '' ? val : 'Nombre del Negocio';
        });

        // Inicializar si ya venía old input
        if (nameInput.value.trim() !== '') {
            mockupName.textContent = nameInput.value.trim();
        }

        // 2. Validación en vivo de la URL de Google Maps
        function validateUrl(url) {
            const trimmed = url.trim();
            if (!trimmed) {
                urlIcon.innerHTML = '<i class="fa-solid fa-circle-question text-slate-300 text-sm"></i>';
                testLinkBtn.classList.add('hidden');
                urlFeedback.innerHTML = 'Pega el enlace oficial que obtienes al pulsar en "Compartir perfil del negocio" o "Pedir reseñas".';
                urlFeedback.className = 'text-[11px] text-slate-400 mt-1';
                return;
            }

            // Expresión regular para dominios válidos de Google Maps
            const isGoogleMaps = /^(https?:\/\/)?((maps\.app\.goo\.gl\/[\w\d]+)|(goo\.gl\/maps\/[\w\d]+)|((www\.)?google\.[a-z\.]+\/maps)|(maps\.google\.[a-z\.]+)|(search\.google\.com\/local\/writereview))/i.test(trimmed);

            if (isGoogleMaps) {
                urlIcon.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>';
                testLinkBtn.href = trimmed;
                testLinkBtn.classList.remove('hidden');
                urlFeedback.innerHTML = '<span class="text-emerald-600 font-medium">✓ Enlace compatible de Google Maps detectado</span>';
            } else {
                urlIcon.innerHTML = '<i class="fa-solid fa-circle-exclamation text-amber-500 text-sm"></i>';
                testLinkBtn.classList.add('hidden');
                urlFeedback.innerHTML = '<span class="text-amber-600">Asegúrate de que el enlace comience con maps.app.goo.gl o google.com/maps</span>';
            }
        }

        urlInput.addEventListener('input', function() {
            validateUrl(this.value);
        });

        if (urlInput.value.trim() !== '') {
            validateUrl(urlInput.value);
        }

        // 3. Manejo de Drag and Drop y Previsualización del Logotipo
        logoInput.addEventListener('change', function(e) {
            handleFileSelection(this.files[0]);
        });

        function handleFileSelection(file) {
            if (!file) return;

            // Validación de tamaño (2.5 MB)
            if (file.size > 2.5 * 1024 * 1024) {
                showToast('El archivo excede los 2.5 MB permitidos', 'error');
                logoInput.value = '';
                return;
            }

            // Validación de extensión
            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowed.includes(file.type)) {
                showToast('Solo se permiten imágenes JPG, PNG o WEBP', 'error');
                logoInput.value = '';
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                mockupLogoImg.src = e.target.result;
                mockupLogoImg.classList.remove('hidden');
                mockupDefaultIcon.classList.add('hidden');

                dropPrompt.classList.add('hidden');
                dropPreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        removeLogoBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            logoInput.value = '';
            dropPreview.classList.add('hidden');
            dropPrompt.classList.remove('hidden');

            mockupLogoImg.src = '';
            mockupLogoImg.classList.add('hidden');
            mockupDefaultIcon.classList.remove('hidden');
        });
    });
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
