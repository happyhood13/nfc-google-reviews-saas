<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Footer Layout & Scripts Vanilla JS
 * ==============================================================================
 */
declare(strict_types=1);
?>
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200 bg-white text-slate-500 text-xs py-8 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            
            <div class="flex items-center gap-2">
                <span class="font-display font-semibold text-slate-700">ReviewNFC Pro</span>
                <span class="text-slate-300">|</span>
                <span>Fase 1: Módulo de Gestión de Negocios, Códigos QR & Tarjetas NFC</span>
            </div>

            <!-- OWASP & Security Badges -->
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-medium">
                    <i class="fa-solid fa-shield-halved text-[10px]"></i> OWASP Top 10 Protected
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px] font-medium">
                    <i class="fa-solid fa-lock text-[10px]"></i> CSRF & SQLi Safe
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-medium">
                    <i class="fa-brands fa-php text-xs"></i> PHP 8.2 + PDO
                </span>
            </div>

        </div>
    </footer>

    <!-- Modal para Vista Rápida del Código QR -->
    <div id="qrModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity" role="dialog" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-100 transform transition-transform animate-scale-up">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 id="modalQrTitle" class="font-display font-bold text-slate-900 text-base truncate">Código QR</h3>
                <button type="button" onclick="closeQrModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Contenedor de la Imagen del QR -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 mb-4 flex items-center justify-center">
                <img id="modalQrImg" src="" alt="Código QR" class="w-56 h-56 object-contain rounded-lg shadow-sm bg-white p-2">
            </div>

            <p class="text-xs text-slate-500 mb-4">Apunta la cámara de tu móvil para verificar que abre las reseñas de Google Maps.</p>

            <!-- Acciones del Modal -->
            <div class="grid grid-cols-2 gap-2 mb-3">
                <a id="modalDownloadSvg" href="#" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 transition shadow-sm">
                    <i class="fa-solid fa-file-code"></i> SVG Vector
                </a>
                <a id="modalDownloadPng" href="#" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 transition shadow-sm">
                    <i class="fa-solid fa-image"></i> PNG HD
                </a>
            </div>

            <button type="button" id="modalCopyBtn" onclick="" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 transition">
                <i class="fa-regular fa-copy"></i> Copiar Enlace Directo
            </button>

        </div>
    </div>

    <!-- Modal de Confirmación para Eliminación Segura -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity" role="dialog" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-100">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="font-display font-bold text-slate-900 text-lg mb-1">¿Eliminar este comercio?</h3>
            <p id="deleteBusinessName" class="text-xs text-slate-500 mb-6">Esta acción es irreversible y eliminará los códigos QR y logotipos asociados.</p>

            <form id="deleteForm" method="POST" action="index.php?action=delete" class="flex gap-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteBusinessId" value="">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-600/20 transition">
                    Sí, Eliminar
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts Vanilla JS para Interactividad -->
    <script>
        // Función universal para copiar enlaces al portapapeles con toast flotante
        function copyToClipboard(text, customMessage = '¡Enlace copiado al portapapeles!') {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast(customMessage, 'success');
                }).catch(() => {
                    fallbackCopy(text, customMessage);
                });
            } else {
                fallbackCopy(text, customMessage);
            }
        }

        function fallbackCopy(text, customMessage) {
            const tempInput = document.createElement('textarea');
            tempInput.value = text;
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.focus();
            tempInput.select();
            try {
                document.execCommand('copy');
                showToast(customMessage, 'success');
            } catch (err) {
                showToast('No se pudo copiar automáticamente', 'error');
            }
            document.body.removeChild(tempInput);
        }

        // Generador dinámico de Notificaciones Toast
        function showToast(message, type = 'success') {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container fixed bottom-5 right-5 z-50 flex flex-col gap-3 max-w-md w-full px-4 pointer-events-none';
                document.body.appendChild(container);
            }

            const isSuccess = type === 'success';
            const bgClass = isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800';
            const iconClass = isSuccess ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-rose-500';

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-xl ${bgClass} transition-all transform duration-300`;
            toast.innerHTML = `
                <i class="fa-solid ${iconClass} text-lg mt-0.5"></i>
                <div class="flex-1 text-sm font-medium leading-relaxed">${escapeHtml(message)}</div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        // Control del Modal de QR
        function openQrModal(qrSrc, title, redirectUrl, downloadSvgUrl, downloadPngUrl) {
            document.getElementById('modalQrImg').src = qrSrc;
            document.getElementById('modalQrTitle').textContent = title;
            document.getElementById('modalDownloadSvg').href = downloadSvgUrl;
            document.getElementById('modalDownloadPng').href = downloadPngUrl;
            document.getElementById('modalCopyBtn').setAttribute('onclick', `copyToClipboard('${redirectUrl}', '¡Enlace del QR copiado!')`);

            const modal = document.getElementById('qrModal');
            modal.classList.remove('hidden');
        }

        function closeQrModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }

        // Control del Modal de Eliminación
        function confirmDelete(id, name) {
            document.getElementById('deleteBusinessId').value = id;
            document.getElementById('deleteBusinessName').textContent = `¿Estás seguro de eliminar el comercio "${name}"? Esta acción no se puede deshacer.`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQrModal();
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
