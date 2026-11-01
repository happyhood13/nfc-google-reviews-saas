"use client";

import { useEffect } from "react";

interface AdSenseBannerProps {
  slot: string;
  format?: "auto" | "fluid" | "rectangle" | "horizontal";
  responsive?: boolean;
  userPlan?: "free" | "pro";
  className?: string;
}

export default function AdSenseBanner({
  slot,
  format = "auto",
  responsive = true,
  userPlan = "free",
  className = "",
}: AdSenseBannerProps) {
  const clientId = process.env.NEXT_PUBLIC_ADSENSE_CLIENT_ID;
  const isDev =
    process.env.NODE_ENV !== "production" ||
    !clientId ||
    clientId.includes("1234567890");

  // Si el usuario tiene plan PRO, no se muestra ningún anuncio
  if (userPlan === "pro") {
    return null;
  }

  useEffect(() => {
    if (!isDev) {
      try {
        ((window as any).adsbygoogle = (window as any).adsbygoogle || []).push({});
      } catch (err) {
        console.error("Error al cargar anuncio de AdSense:", err);
      }
    }
  }, [isDev]);

  if (isDev) {
    return (
      <div
        className={`p-4 border border-dashed border-slate-300 rounded-2xl bg-slate-50 text-center my-4 ${className}`}
      >
        <div className="flex items-center justify-center gap-2 text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">
          <span>Espacio Publicitario Google AdSense</span>
          <span className="px-1.5 py-0.5 rounded bg-slate-200 text-[10px]">Mock Dev</span>
        </div>
        <p className="text-[11px] text-slate-500">
          Slot: <code className="text-brand-600 font-mono font-semibold">{slot}</code> | Se oculta automáticamente para suscriptores del <strong>Plan Pro</strong>.
        </p>
      </div>
    );
  }

  return (
    <div className={`overflow-hidden my-4 text-center ${className}`}>
      <ins
        className="adsbygoogle"
        style={{ display: "block" }}
        data-ad-client={clientId}
        data-ad-slot={slot}
        data-ad-format={format}
        data-full-width-responsive={responsive ? "true" : "false"}
      />
    </div>
  );
}
