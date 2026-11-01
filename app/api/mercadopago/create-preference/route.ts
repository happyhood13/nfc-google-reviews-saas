import { NextResponse } from "next/server";
import { createClient } from "@/lib/supabase/server";

export const dynamic = "force-dynamic";

export async function POST() {
  const supabase = createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    return NextResponse.json({ error: "No autenticado" }, { status: 401 });
  }

  const accessToken = process.env.MERCADOPAGO_ACCESS_TOKEN;
  const appUrl = process.env.NEXT_PUBLIC_APP_URL?.replace(/\/$/, "");

  if (!accessToken) {
    return NextResponse.json({ error: "Mercado Pago no está configurado" }, { status: 500 });
  }
  if (!appUrl) {
    return NextResponse.json({ error: "NEXT_PUBLIC_APP_URL no está configurado" }, { status: 500 });
  }

  const { data: profile } = await supabase
    .from("profiles")
    .select("email, full_name, plan")
    .eq("id", user.id)
    .single();

  if (profile?.plan === "pro") {
    return NextResponse.json({ error: "Tu cuenta ya tiene el plan Pro" }, { status: 409 });
  }

  const preference = await fetch("https://api.mercadopago.com/checkout/preferences", {
    method: "POST",
    headers: {
      Authorization: `Bearer ${accessToken}`,
      "Content-Type": "application/json",
      "X-Idempotency-Key": crypto.randomUUID(),
    },
    body: JSON.stringify({
      external_reference: `reviewnfc:${user.id}`,
      purpose: "wallet_purchase",
      payer: {
        email: profile?.email || user.email,
        name: profile?.full_name || undefined,
      },
      items: [
        {
          id: "reviewnfc-pro-monthly",
          title: "ReviewNFC Pro mensual",
          description: "Tarjetas NFC y códigos QR ilimitados, sin anuncios y con analíticas.",
          quantity: 1,
          currency_id: process.env.MERCADOPAGO_CURRENCY_ID || "USD",
          unit_price: Number(process.env.MERCADOPAGO_PRO_PRICE || "9.99"),
        },
      ],
      metadata: {
        user_id: user.id,
        plan: "pro",
      },
      back_urls: {
        success: `${appUrl}/api/mercadopago/success`,
        failure: `${appUrl}/dashboard?payment=failure`,
        pending: `${appUrl}/dashboard?payment=pending`,
      },
      auto_return: "approved",
      notification_url: `${appUrl}/api/mercadopago/webhook`,
    }),
  });

  const result = await preference.json();

  if (!preference.ok) {
    console.error("Mercado Pago preference error", result);
    return NextResponse.json({ error: "No se pudo crear el pago" }, { status: 502 });
  }

  return NextResponse.json({
    checkoutUrl: result.init_point,
    sandboxCheckoutUrl: result.sandbox_init_point,
  });
}
