import { createClient } from "@supabase/supabase-js";
import { NextResponse } from "next/server";

export const dynamic = "force-dynamic";

// Usamos el cliente administrativo con SERVICE_ROLE_KEY para ejecutar la función RPC sin requerir login del usuario final
const supabaseAdmin = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL || "https://xyzcompany.supabase.co",
  process.env.SUPABASE_SERVICE_ROLE_KEY || "dummy_service_key"
);

export async function GET(
  request: Request,
  { params }: { params: { slug: string } }
) {
  const slug = params.slug;

  if (!slug) {
    return new NextResponse("Slug no proporcionado", { status: 400 });
  }

  try {
    // Invocar la función RPC atómica de Supabase para registrar el tap y obtener el destino
    const { data, error } = await supabaseAdmin.rpc("record_nfc_scan", {
      target_slug: slug,
    });

    if (error || !data || data.length === 0) {
      return new NextResponse(
        `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Comercio No Encontrado</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4 text-center">
        <div class="max-w-sm">
          <div class="w-16 h-16 bg-red-500/20 text-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold">✕</div>
          <h1 class="text-xl font-bold mb-2">Tarjeta no disponible</h1>
          <p class="text-xs text-slate-400">Esta tarjeta NFC o código QR no está activo o ha sido dado de baja por el comercio.</p>
        </div></body></html>`,
        { status: 404, headers: { "Content-Type": "text/html; charset=utf-8" } }
      );
    }

    const business = data[0];

    // Redirección 302 segura hacia Google Maps Reviews
    return NextResponse.redirect(business.redirect_url, { status: 302 });
  } catch (err) {
    console.error("Error en redirección NFC:", err);
    return new NextResponse("Error interno del servidor", { status: 500 });
  }
}
