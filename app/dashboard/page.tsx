import { createClient } from "@/lib/supabase/server";
import { redirect } from "next/navigation";
import Link from "next/link";
import { Plus, Store, Wifi, ArrowUpRight, Crown, AlertTriangle } from "lucide-react";
import AdSenseBanner from "@/components/ads/AdSenseBanner";

export const dynamic = "force-dynamic";

export default async function DashboardPage() {
  const supabase = createClient();
  const { data: { user } } = await supabase.auth.getUser();

  if (!user) redirect("/login");

  // 1. Obtener perfil del usuario (Plan Free vs Pro)
  const { data: profile } = await supabase
    .from("profiles")
    .select("*")
    .eq("id", user.id)
    .single();

  const userPlan = (profile?.plan || "free") as "free" | "pro";

  // 2. Obtener negocios del usuario (Aislados automáticamente por RLS)
  const { data: businesses } = await supabase
    .from("businesses")
    .select("*")
    .order("created_at", { ascending: false });

  const totalBusinesses = businesses?.length || 0;
  const totalScans = businesses?.reduce((acc, curr) => acc + (curr.total_scans || 0), 0) || 0;
  const isFreeLimitReached = userPlan === "free" && totalBusinesses >= 1;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      {/* Alerta de Límite en Plan Free */}
      {isFreeLimitReached && (
        <div className="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 animate-fade-in">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
              <AlertTriangle className="w-5 h-5" />
            </div>
            <div>
              <p className="text-xs font-bold text-amber-800 uppercase tracking-wider">
                Límite del Plan Free Alcanzado (1 de 1 tarjeta creada)
              </p>
              <p className="text-xs text-amber-700 mt-0.5">
                Pasa al <strong>Plan Pro</strong> para crear comercios ilimitados, eliminar anuncios de AdSense y desbloquear analíticas avanzadas.
              </p>
            </div>
          </div>
          <button 
            type="button"
            className="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex-shrink-0"
          >
            Actualizar a Pro ($9.99/mes)
          </button>
        </div>
      )}

      {/* Encabezado del Dashboard */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 className="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
            Panel de Tarjetas & Reseñas
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-1">
            Gestiona tus comercios asociados, chips NFC inteligentes y códigos QR de reseñas.
          </p>
        </div>

        <div>
          {isFreeLimitReached ? (
            <button 
              disabled 
              title="Límite del Plan Free alcanzado (máx. 1 comercio)"
              className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-400 bg-slate-100 cursor-not-allowed border border-slate-200"
            >
              <Plus className="w-4 h-4" /> Nuevo Negocio (Límite 1/1)
            </button>
          ) : (
            <Link
              href="/dashboard/new"
              className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-md shadow-brand-600/25 active:scale-95 transition"
            >
              <Plus className="w-4 h-4" /> Registrar Nuevo Negocio
            </Link>
          )}
        </div>
      </div>

      {/* Tarjetas de Métricas SaaS */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Comercios Activos</span>
            <div className="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
              <Store className="w-4 h-4" />
            </div>
          </div>
          <p className="text-3xl font-extrabold font-display text-slate-900">{totalBusinesses}</p>
          <span className="text-[11px] text-slate-400 mt-1 block">
            Plan actual: <strong className="uppercase text-brand-600 font-bold">{userPlan}</strong> ({totalBusinesses}/{userPlan === 'free' ? '1' : '∞'})
          </span>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Taps NFC & Escaneos</span>
            <div className="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Wifi className="w-4 h-4 rotate-90" />
            </div>
          </div>
          <p className="text-3xl font-extrabold font-display text-slate-900">{totalScans}</p>
          <span className="text-[11px] text-emerald-600 font-medium mt-1 block">Interacciones de clientes registradas</span>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nivel de Suscripción</span>
            <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
              userPlan === 'pro' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600'
            }`}>
              {userPlan}
            </span>
          </div>
          <p className="text-sm font-bold text-slate-800 mt-2 flex items-center gap-1.5">
            {userPlan === 'pro' && <Crown className="w-4 h-4 text-amber-500 flex-shrink-0" />}
            {userPlan === 'pro' ? 'Acceso Ilimitado Pro' : 'Plan Gratuito con Anuncios'}
          </p>
          <p className="text-[11px] text-slate-400 mt-0.5">
            {userPlan === 'pro' ? 'Sin límites ni marcas de agua' : 'Google AdSense activo en la cuenta'}
          </p>
        </div>
      </div>

      {/* Espacio Publicitario AdSense 1 (Solo se renderiza si el plan es Free) */}
      <AdSenseBanner
        slot={process.env.NEXT_PUBLIC_ADSENSE_DASHBOARD_SLOT || "1234567890"}
        userPlan={userPlan}
        className="mb-8"
      />

      {/* Tabla de Negocios Registrados */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        {totalBusinesses === 0 ? (
          <div className="p-12 text-center">
            <div className="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
              <Store className="w-8 h-8" />
            </div>
            <h3 className="font-display font-bold text-slate-900 text-base mb-1">Aún no tienes comercios registrados</h3>
            <p className="text-xs text-slate-500 max-w-sm mx-auto mb-6">
              Crea tu primer perfil para generar automáticamente el código QR y el enlace para tarjetas inteligentes NFC.
            </p>
            <Link
              href="/dashboard/new"
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white rounded-xl text-xs font-bold hover:bg-brand-500 shadow-md shadow-brand-600/20 transition"
            >
              <Plus className="w-4 h-4" /> Registrar Primer Negocio
            </Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-xs sm:text-sm">
              <thead>
                <tr className="bg-slate-50 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                  <th className="py-3.5 px-4 sm:px-6">Comercio</th>
                  <th className="py-3.5 px-4">Teléfono</th>
                  <th className="py-3.5 px-4 text-center">Taps NFC</th>
                  <th className="py-3.5 px-4 sm:px-6 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {businesses?.map((b) => (
                  <tr key={b.id} className="hover:bg-slate-50/60 transition group">
                    <td className="py-4 px-4 sm:px-6">
                      <div className="flex items-center gap-3">
                        {b.logo_url ? (
                          <img src={b.logo_url} alt={b.name} className="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm flex-shrink-0" />
                        ) : (
                          <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-sm">
                            {b.name.substring(0, 2).toUpperCase()}
                          </div>
                        )}
                        <div>
                          <Link href={`/dashboard/businesses/${b.id}`} className="font-bold text-slate-900 hover:text-brand-600 transition block text-sm">
                            {b.name}
                          </Link>
                          <span className="text-[11px] font-mono text-slate-400">/r/{b.slug}</span>
                        </div>
                      </div>
                    </td>
                    <td className="py-4 px-4 text-slate-700 font-medium whitespace-nowrap">{b.phone}</td>
                    <td className="py-4 px-4 text-center whitespace-nowrap">
                      <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <Wifi className="w-3 h-3 rotate-90" /> {b.total_scans}
                      </span>
                    </td>
                    <td className="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                      <Link
                        href={`/dashboard/businesses/${b.id}`}
                        className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 transition"
                      >
                        Ver Ficha & Imprimir <ArrowUpRight className="w-3.5 h-3.5" />
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

    </div>
  );
}
