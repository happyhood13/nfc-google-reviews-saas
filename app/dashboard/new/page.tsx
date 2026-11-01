"use client";

import { useState } from "react";
import { createClient } from "@/lib/supabase/client";
import { useRouter } from "next/navigation";
import { Store, Phone, Globe, Upload, ArrowLeft, Wand2, AlertCircle, Star, Wifi } from "lucide-react";
import Link from "next/link";
import { isValidGoogleMapsUrl, generateSlug } from "@/lib/utils";

export default function NewBusinessPage() {
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [googleMapsUrl, setGoogleMapsUrl] = useState("");
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const router = useRouter();
  const supabase = createClient();

  const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (file.size > 2.5 * 1024 * 1024) {
      setError("El logotipo no debe superar los 2.5 MB");
      return;
    }

    setLogoFile(file);
    setLogoPreview(URL.createObjectURL(file));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    // 1. Validar dominio oficial de Google Maps
    if (!isValidGoogleMapsUrl(googleMapsUrl)) {
      setError("Debes ingresar un enlace oficial de Google Maps (ej: https://maps.app.goo.gl/... o https://goo.gl/maps/...)");
      setLoading(false);
      return;
    }

    const { data: { user } } = await supabase.auth.getUser();
    if (!user) {
      router.push("/login");
      return;
    }

    try {
      // 2. Comprobar cuota del Plan Free (máx. 1 negocio)
      const { data: profile } = await supabase
        .from("profiles")
        .select("plan")
        .eq("id", user.id)
        .single();

      const userPlan = profile?.plan || "free";

      if (userPlan === "free") {
        const { count } = await supabase
          .from("businesses")
          .select("id", { count: "exact", head: true })
          .eq("user_id", user.id);

        if ((count || 0) >= 1) {
          setError("Has alcanzado el límite de 1 comercio del Plan Free. Actualiza a Pro para tarjetas ilimitadas.");
          setLoading(false);
          return;
        }
      }

      // 3. Subir logo a Supabase Storage
      let logoUrl: string | null = null;
      if (logoFile) {
        const fileExt = logoFile.name.split(".").pop();
        const filePath = `${user.id}/${Date.now()}.${fileExt}`;
        
        const { error: uploadError } = await supabase.storage
          .from("logos")
          .upload(filePath, logoFile);

        if (uploadError) throw uploadError;

        const { data: publicUrlData } = supabase.storage
          .from("logos")
          .getPublicUrl(filePath);

        logoUrl = publicUrlData.publicUrl;
      }

      // 4. Crear Slug único
      const baseSlug = generateSlug(name) || "negocio";
      const uniqueSlug = `${baseSlug}-${Math.random().toString(36).substring(2, 6)}`;

      // 5. Insertar en PostgreSQL (RLS verifica automáticamente)
      const { data: newBusiness, error: insertError } = await supabase
        .from("businesses")
        .insert({
          user_id: user.id,
          name,
          phone,
          google_maps_url: googleMapsUrl,
          logo_url: logoUrl,
          slug: uniqueSlug,
        })
        .select()
        .single();

      if (insertError) throw insertError;

      router.push(`/dashboard/businesses/${newBusiness.id}`);
      router.refresh();
    } catch (err: any) {
      setError(err.message || "Error al registrar el comercio");
      setLoading(false);
    }
  };

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <Link href="/dashboard" className="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition mb-6">
        <ArrowLeft className="w-3.5 h-3.5" /> Volver al Dashboard
      </Link>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Formulario (7 columnas) */}
        <div className="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
          <h1 className="font-display font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight mb-1">
            Registrar Nuevo Comercio
          </h1>
          <p className="text-xs text-slate-500 mb-6">
            Configura la información para generar el código QR y la ficha para tarjetas NFC.
          </p>

          {error && (
            <div className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
              <AlertCircle className="w-4 h-4 flex-shrink-0" />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Nombre del Negocio <span className="text-rose-500">*</span>
              </label>
              <div className="relative">
                <Store className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Ej: Café & Bistro La Roma"
                  className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Teléfono / WhatsApp <span className="text-rose-500">*</span>
              </label>
              <div className="relative">
                <Phone className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="tel"
                  required
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="+52 55 1234 5678"
                  className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Logotipo del Comercio <span className="text-slate-400 font-normal">(Opcional)</span>
              </label>
              <div className="border-2 border-dashed border-slate-200 hover:border-brand-500/60 rounded-2xl p-4 text-center bg-slate-50 transition cursor-pointer">
                <input
                  type="file"
                  id="logo"
                  accept="image/png, image/jpeg, image/webp"
                  onChange={handleLogoChange}
                  className="hidden"
                />
                <label htmlFor="logo" className="cursor-pointer flex flex-col items-center justify-center gap-1">
                  <Upload className="w-6 h-6 text-brand-600 mb-1" />
                  <span className="text-xs font-bold text-slate-700">Subir imagen</span>
                  <span className="text-[10px] text-slate-400">PNG, JPG o WEBP (Máx. 2.5 MB)</span>
                </label>
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Enlace de Reseñas de Google Maps <span className="text-rose-500">*</span>
              </label>
              <div className="relative">
                <Globe className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="url"
                  required
                  value={googleMapsUrl}
                  onChange={(e) => setGoogleMapsUrl(e.target.value)}
                  placeholder="https://maps.app.goo.gl/..."
                  className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition"
                />
              </div>
              <p className="text-[11px] text-slate-400 mt-1">
                Abre Google Maps &gt; Compartir perfil del negocio &gt; Copia el enlace oficial.
              </p>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 px-4 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-bold shadow-md shadow-brand-600/25 transition flex items-center justify-center gap-2 mt-4 active:scale-95 disabled:opacity-50"
            >
              <Wand2 className="w-4 h-4" />
              {loading ? "Creando en la nube..." : "Generar Tarjeta NFC & Código QR"}
            </button>
          </form>
        </div>

        {/* Maqueta en Tiempo Real (5 columnas) */}
        <div className="lg:col-span-5 sticky top-24 space-y-4">
          <div className="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">
              Vista Previa en Vivo
            </span>

            {/* Tarjeta NFC Frontal */}
            <div className="aspect-[85/54] rounded-2xl p-5 text-white bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 border border-slate-800 shadow-xl flex flex-col justify-between relative overflow-hidden">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2.5">
                  {logoPreview ? (
                    <img src={logoPreview} alt="Logo" className="w-8 h-8 rounded-lg object-cover" />
                  ) : (
                    <div className="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold text-xs">
                      {name ? name.substring(0, 2).toUpperCase() : "NE"}
                    </div>
                  )}
                  <div>
                    <p className="font-bold text-xs truncate max-w-[140px]">{name || "Nombre del Negocio"}</p>
                    <p className="text-[9px] text-slate-400">Reseñas en Google</p>
                  </div>
                </div>
                <Wifi className="w-4 h-4 rotate-90 text-white/80" />
              </div>

              <div className="text-center my-auto">
                <div className="flex items-center justify-center gap-1 text-amber-400 text-sm mb-1">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="w-3.5 h-3.5 fill-amber-400" />
                  ))}
                </div>
                <p className="text-[10px] text-slate-300 font-semibold uppercase tracking-wider">¡Califícanos con 5 Estrellas!</p>
              </div>

              <div className="flex items-center justify-between text-[9px] text-slate-400 border-t border-white/10 pt-2">
                <span>Acerca tu teléfono aquí</span>
                <span className="font-semibold text-slate-200">Google Reviews</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  );
}
