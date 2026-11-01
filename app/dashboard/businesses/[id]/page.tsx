"use client";

import { useEffect, useState } from "react";
import { createClient } from "@/lib/supabase/client";
import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { ArrowLeft, Printer, Download, Copy, Check, ExternalLink, Wifi, Star } from "lucide-react";
import { QRCodeSVG } from "qrcode.react";

export default function BusinessDetailPage() {
  const { id } = useParams();
  const [business, setBusiness] = useState<any>(null);
  const [copied, setCopied] = useState(false);
  const [viewMode, setViewMode] = useState<"card" | "stand">("card");
  const supabase = createClient();
  const router = useRouter();

  useEffect(() => {
    async function loadData() {
      const { data, error } = await supabase
        .from("businesses")
        .select("*")
        .eq("id", id)
        .single();

      if (error || !data) {
        router.push("/dashboard");
      } else {
        setBusiness(data);
      }
    }
    loadData();
  }, [id, router, supabase]);

  if (!business) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <p className="text-xs font-semibold text-slate-400">Cargando datos del comercio...</p>
      </div>
    );
  }

  const nfcRedirectUrl = typeof window !== "undefined" 
    ? `${window.location.origin}/r/${business.slug}`
    : `https://tudominio.com/r/${business.slug}`;

  const copyUrl = () => {
    navigator.clipboard.writeText(nfcRedirectUrl);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const downloadSvg = () => {
    const svgElement = document.getElementById("qr-svg-code");
    if (!svgElement) return;
    const svgData = new XMLSerializer().serializeToString(svgElement);
    const blob = new Blob([svgData], { type: "image/svg+xml;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const downloadLink = document.createElement("a");
    downloadLink.href = url;
    downloadLink.download = `qr_${business.slug}.svg`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
  };

  return (
    <div className="max-w-5xl mx-auto px-4 py-8">
      
      {/* Barra de Acciones y Navegación (Oculta en Impresión) */}
      <div className="print:hidden mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
        <div className="flex items-center gap-3">
          <Link
            href="/dashboard"
            className="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition"
          >
            <ArrowLeft className="w-4 h-4" />
          </Link>
          <div>
            <h1 className="font-display font-bold text-lg text-slate-900 leading-tight">{business.name}</h1>
            <p className="text-[11px] text-slate-400">Taps registrados: <strong className="text-brand-600">{business.total_scans}</strong></p>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={() => setViewMode(viewMode === "card" ? "stand" : "card")}
            className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition"
          >
            Ver {viewMode === "card" ? "Expositor A6" : "Tarjeta NFC"}
          </button>
          
          <button
            onClick={downloadSvg}
            className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-sm"
          >
            <Download className="w-3.5 h-3.5" /> Descargar SVG
          </button>

          <button
            onClick={() => window.print()}
            className="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md shadow-brand-600/20 active:scale-95 transition"
          >
            <Printer className="w-3.5 h-3.5" /> Imprimir Ficha
          </button>
        </div>
      </div>

      {/* Contenedor Imprimible y de Maquetas Físicas */}
      <div className="flex flex-col items-center justify-center">
        
        {viewMode === "card" ? (
          /* Maqueta de Tarjeta NFC Física (85.6 x 53.98 mm) */
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-3xl">
            
            {/* Frontal Anverso */}
            <div className="print-area aspect-[85/54] rounded-3xl p-6 text-white bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 border border-slate-800 shadow-2xl flex flex-col justify-between">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  {business.logo_url && (
                    <img src={business.logo_url} alt="Logo" className="w-10 h-10 rounded-xl object-cover bg-white/10 p-1 border border-white/20" />
                  )}
                  <div>
                    <h3 className="font-bold text-sm tracking-tight">{business.name}</h3>
                    <p className="text-[10px] text-slate-400">Reseñas en Google</p>
                  </div>
                </div>
                <Wifi className="w-5 h-5 rotate-90 text-white/80" />
              </div>

              <div className="text-center my-auto">
                <div className="flex items-center justify-center gap-1 text-amber-400 text-lg mb-1">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="w-4 h-4 fill-amber-400" />
                  ))}
                </div>
                <p className="text-xs font-bold uppercase tracking-wider text-slate-200">¡Califícanos con 5 Estrellas!</p>
              </div>

              <div className="flex items-center justify-between text-[10px] text-slate-400 border-t border-white/10 pt-2.5">
                <span>Acerca tu teléfono aquí</span>
                <span className="font-semibold text-slate-200">Google Maps NFC</span>
              </div>
            </div>

            {/* Reverso con QR de Respaldo */}
            <div className="print-area aspect-[85/54] rounded-3xl p-6 bg-white border border-slate-200 shadow-2xl flex items-center justify-between gap-4">
              <div className="flex-1 flex flex-col justify-between h-full py-1">
                <div>
                  <h4 className="font-bold text-xs text-slate-900">{business.name}</h4>
                  <p className="text-[11px] text-brand-600 font-bold mt-1">¿Tu móvil no tiene NFC?</p>
                  <p className="text-[9px] text-slate-500 mt-1 leading-relaxed">
                    Apunta con la cámara de tu smartphone hacia este código QR para abrir el formulario de reseñas de Google Maps.
                  </p>
                </div>
                <span className="text-[9px] font-mono text-slate-400">/r/{business.slug}</span>
              </div>

              <div className="w-24 h-24 p-2 border border-slate-200 rounded-2xl bg-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <QRCodeSVG id="qr-svg-code" value={nfcRedirectUrl} size={80} level="M" />
              </div>
            </div>

          </div>
        ) : (
          /* Maqueta de Expositor de Mesa / Acrílico A6 (105 x 148 mm) */
          <div className="print-area w-full max-w-sm bg-white border border-slate-200 rounded-3xl p-8 text-center shadow-2xl">
            {business.logo_url && (
              <img src={business.logo_url} alt="Logo" className="w-16 h-16 rounded-2xl object-cover mx-auto mb-3 border p-1 bg-slate-50" />
            )}
            <h2 className="font-display font-extrabold text-2xl text-slate-900 mb-1">{business.name}</h2>
            <p className="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">¡Tu opinión nos ayuda a crecer!</p>

            <div className="flex items-center justify-center gap-1.5 text-amber-400 text-xl mb-4">
              {[...Array(5)].map((_, i) => (
                <Star key={i} className="w-5 h-5 fill-amber-400" />
              ))}
            </div>

            <div className="inline-block p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl shadow-inner mb-4">
              <QRCodeSVG value={nfcRedirectUrl} size={180} level="Q" />
            </div>

            <div className="grid grid-cols-2 gap-2 text-center text-xs text-slate-600 font-medium pt-3 border-t border-slate-100">
              <p>1. Escanea con tu cámara</p>
              <p>2. Deja tus 5 estrellas</p>
            </div>
          </div>
        )}

      </div>

      {/* Datos Técnicos de Grabación de Chip NFC (Oculta en Impresión) */}
      <div className="print:hidden mt-12 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm max-w-2xl mx-auto">
        <h4 className="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          Enlace a Grabar en el Chip NFC (NTAG213 / NTAG215)
        </h4>
        <div className="flex items-center gap-2">
          <input
            type="text"
            readOnly
            value={nfcRedirectUrl}
            className="flex-1 bg-slate-50 border rounded-xl px-3 py-2 text-xs font-mono text-slate-700 select-all outline-none"
          />
          <button
            onClick={copyUrl}
            className="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition"
          >
            {copied ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
            {copied ? "Copiado" : "Copiar"}
          </button>
        </div>
        <p className="text-[11px] text-slate-400 mt-2">
          Usa la aplicación gratuita <strong>NFC Tools</strong> (Android / iOS) para escribir este enlace directamente en tus tarjetas o pegatinas NFC.
        </p>
      </div>

    </div>
  );
}
