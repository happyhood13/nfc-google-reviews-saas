import Link from "next/link";
import { Wifi, Sparkles, Star, ArrowRight, ShieldCheck, Printer, BarChart3, Check } from "lucide-react";

export default function HomePage() {
  return (
    <div className="relative overflow-hidden">
      
      {/* Hero Section */}
      <section className="relative pt-20 pb-28 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-center">
        
        {/* Glow de fondo */}
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-gradient-to-tr from-brand-500/20 to-cyan-400/20 blur-3xl rounded-full pointer-events-none"></div>

        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold mb-6">
          <Sparkles className="w-3.5 h-3.5" /> Nueva Plataforma SaaS Multi-usuario
        </div>

        <h1 className="font-display font-extrabold text-4xl sm:text-6xl text-slate-900 tracking-tight max-w-4xl mx-auto leading-tight sm:leading-tight">
          Multiplica las reseñas de tu negocio con <span className="text-brand-600">Tarjetas NFC & Códigos QR</span>
        </h1>

        <p className="mt-6 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
          Permite a tus clientes calificar con 5 estrellas en Google Maps simplemente acercando su teléfono inteligente o escaneando un código QR dinámico.
        </p>

        <div className="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link
            href="/register"
            className="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm shadow-xl shadow-brand-600/25 active:scale-95 transition flex items-center justify-center gap-2"
          >
            <span>Crear Mi Primera Tarjeta Gratis</span>
            <ArrowRight className="w-4 h-4" />
          </Link>
          <Link
            href="/login"
            className="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-sm transition"
          >
            Iniciar Sesión
          </Link>
        </div>

        {/* Mockup de la Tarjeta NFC en el Hero */}
        <div className="mt-16 max-w-md mx-auto aspect-[85/54] rounded-3xl p-6 text-white bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 border border-slate-700/80 shadow-2xl flex flex-col justify-between text-left relative overflow-hidden group hover:scale-105 transition-transform duration-500">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center font-bold text-sm">
                CR
              </div>
              <div>
                <h3 className="font-bold text-sm">Café & Bistro La Roma</h3>
                <p className="text-[10px] text-slate-400">Reseñas en Google</p>
              </div>
            </div>
            <Wifi className="w-5 h-5 rotate-90 text-white/80" />
          </div>

          <div className="text-center my-auto">
            <div className="flex items-center justify-center gap-1 text-amber-400 text-base mb-1">
              {[...Array(5)].map((_, i) => (
                <Star key={i} className="w-4 h-4 fill-amber-400" />
              ))}
            </div>
            <p className="text-xs font-semibold tracking-wide text-slate-200 uppercase">
              ¡Califícanos con 5 Estrellas!
            </p>
          </div>

          <div className="flex items-center justify-between text-[10px] text-slate-400 border-t border-white/10 pt-2.5">
            <span>Acerca tu teléfono aquí</span>
            <span className="font-semibold text-slate-200">Google Maps NFC</span>
          </div>
        </div>

      </section>

      {/* Características Clave */}
      <section className="py-20 bg-white border-y border-slate-200/80">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="font-display font-extrabold text-3xl text-slate-900 tracking-tight">
              Diseñado para dueños de comercios y agencias
            </h2>
            <p className="text-sm text-slate-500 mt-2">
              Todo lo necesario para emitir tarjetas NFC, stands acrílicos e imprimir en alta resolución.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
              <div className="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                <Wifi className="w-6 h-6 rotate-90" />
              </div>
              <h3 className="font-bold text-slate-900 text-base mb-2">Redirección Inteligente</h3>
              <p className="text-xs text-slate-500 leading-relaxed">
                El chip NFC y el QR apuntan a tu enlace dinámico. Si cambias de dirección o perfil en Google, nunca perderás tus tarjetas físicas impresas.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
              <div className="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <Printer className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-slate-900 text-base mb-2">Listos para Imprenta</h3>
              <p className="text-xs text-slate-500 leading-relaxed">
                Descarga códigos QR vectoriales en SVG de resolución infinita para imprenta offset o serigrafía en tarjetas de PVC y acrílicos.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                <BarChart3 className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-slate-900 text-base mb-2">Conteo de Taps en Tiempo Real</h3>
              <p className="text-xs text-slate-500 leading-relaxed">
                Mide cuántos clientes interactúan con tus tarjetas en cada punto de venta y optimiza la recolección de opiniones positivas.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Planes: Free vs Pro */}
      <section className="py-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="font-display font-extrabold text-3xl text-slate-900 tracking-tight">
            Planes Simples y Transparentes
          </h2>
          <p className="text-sm text-slate-500 mt-2">
            Comienza gratis con publicidad integrada o desbloquea todo el potencial con el Plan Pro.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
          
          {/* Plan Free */}
          <div className="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
              <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Plan Básico</span>
              <div className="mt-3 flex items-baseline gap-1">
                <span className="text-4xl font-extrabold text-slate-900 font-display">$0</span>
                <span className="text-xs text-slate-400">/ para siempre</span>
              </div>
              <p className="text-xs text-slate-500 mt-2 mb-6">
                Ideal para probar la plataforma con 1 o 2 sucursales.
              </p>

              <ul className="space-y-3 text-xs text-slate-600 mb-8">
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-500" />
                  <span>Hasta 2 Comercios / Tarjetas activas</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-500" />
                  <span>Generador de Código QR vectorial</span>
                </li>
                <li className="flex items-center gap-2 text-slate-400">
                  <span>• Contiene anuncios discretos de Google AdSense</span>
                </li>
                <li className="flex items-center gap-2 text-slate-400">
                  <span>• Marca de agua sutil en plantillas de impresión</span>
                </li>
              </ul>
            </div>

            <Link
              href="/register"
              className="w-full py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-800 font-bold text-xs text-center transition"
            >
              Comenzar con Plan Free
            </Link>
          </div>

          {/* Plan Pro */}
          <div className="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-8 shadow-xl border border-slate-700 flex flex-col justify-between relative overflow-hidden">
            <div className="absolute top-0 right-0 bg-brand-600 text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider">
              Recomendado
            </div>

            <div>
              <span className="text-xs font-bold text-brand-300 uppercase tracking-wider">Plan Profesional</span>
              <div className="mt-3 flex items-baseline gap-1">
                <span className="text-4xl font-extrabold text-white font-display">$9.99</span>
                <span className="text-xs text-slate-400">/ mes</span>
              </div>
              <p className="text-xs text-slate-300 mt-2 mb-6">
                Para negocios consolidados, agencias y franquicias.
              </p>

              <ul className="space-y-3 text-xs text-slate-200 mb-8">
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-400" />
                  <span><strong>Tarjetas y Comercios Ilimitados</strong></span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-400" />
                  <span><strong>100% Libre de Anuncios</strong></span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-400" />
                  <span>Sin marcas de agua en impresiones</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-400" />
                  <span>Analíticas de Taps & Escaneos avanzadas</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-400" />
                  <span>Soporte prioritario por WhatsApp</span>
                </li>
              </ul>
            </div>

            <Link
              href="/register"
              className="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs text-center shadow-lg shadow-brand-600/30 transition"
            >
              Probar Plan Pro
            </Link>
          </div>

        </div>
      </section>

    </div>
  );
}
