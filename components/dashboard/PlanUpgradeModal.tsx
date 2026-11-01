"use client";

import { Check, Sparkles, X } from "lucide-react";

interface PlanUpgradeModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function PlanUpgradeModal({ isOpen, onClose }: PlanUpgradeModalProps) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
      <div className="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 border border-slate-100 relative">
        <button
          onClick={onClose}
          className="absolute top-5 right-5 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
        >
          <X className="w-4 h-4" />
        </button>

        <div className="text-center mb-6">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center mx-auto mb-3 shadow-lg shadow-brand-500/25">
            <Sparkles className="w-6 h-6" />
          </div>
          <h3 className="font-display font-extrabold text-2xl text-slate-900 tracking-tight">
            Pasa al Plan Pro
          </h3>
          <p className="text-xs text-slate-500 mt-1">
            Elimina límites y obtén el máximo potencial de tus tarjetas y reseñas.
          </p>
        </div>

        <div className="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 mb-6">
          <div className="flex items-baseline justify-between mb-4 pb-4 border-b border-white/10">
            <div>
              <span className="text-xs text-slate-400 uppercase font-semibold">Suscripción Mensual</span>
              <h4 className="text-xl font-bold">Plan Ilimitado</h4>
            </div>
            <div className="text-right">
              <span className="text-3xl font-extrabold font-display">$9.99</span>
              <span className="text-xs text-slate-400"> /mes</span>
            </div>
          </div>

          <ul className="space-y-2.5 text-xs text-slate-300">
            <li className="flex items-center gap-2">
              <Check className="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span><strong>Tarjetas NFC y QRs ilimitados</strong> (Plan Free: máx. 1)</span>
            </li>
            <li className="flex items-center gap-2">
              <Check className="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span><strong>Experiencia 100% libre de anuncios</strong> (Sin Google AdSense)</span>
            </li>
            <li className="flex items-center gap-2">
              <Check className="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span><strong>Plantillas sin marca de agua</strong> para stands de acrílico</span>
            </li>
            <li className="flex items-center gap-2">
              <Check className="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span><strong>Analíticas avanzadas</strong> de escaneos y taps NFC</span>
            </li>
          </ul>
        </div>

        <button
          onClick={() => {
            alert("Redirigiendo a pasarela de pago segura (Stripe / Mercado Pago)...");
          }}
          className="w-full py-3 px-4 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-bold shadow-md shadow-brand-600/25 active:scale-95 transition"
        >
          Mejorar a Plan Pro Ahora
        </button>

        <p className="text-center text-[11px] text-slate-400 mt-3">
          Cancela en cualquier momento con un solo clic. Garantía de reembolso de 14 días.
        </p>
      </div>
    </div>
  );
}
