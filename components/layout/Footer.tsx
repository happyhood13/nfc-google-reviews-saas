import AdSenseBanner from "@/components/ads/AdSenseBanner";

export default function Footer({ userPlan = "free" }: { userPlan?: "free" | "pro" }) {
  return (
    <footer className="mt-auto border-t border-slate-200 bg-white text-slate-500 text-xs py-8 print:hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Banner AdSense de Pie de Página (Solo usuarios Free) */}
        <AdSenseBanner
          slot={process.env.NEXT_PUBLIC_ADSENSE_FOOTER_SLOT || "0987654321"}
          userPlan={userPlan}
          className="mb-6"
        />

        <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <span className="font-display font-bold text-slate-800">ReviewNFC Pro</span>
            <span className="text-slate-300">|</span>
            <span>Plataforma SaaS Multi-inquilino de Tarjetas NFC y QRs</span>
          </div>

          <div className="flex items-center gap-4 text-[11px] text-slate-400">
            <span>Supabase PostgreSQL + RLS</span>
            <span>•</span>
            <span>Vercel Edge Ready</span>
            <span>•</span>
            <span>Google AdSense Monetized</span>
          </div>
        </div>

      </div>
    </footer>
  );
}
