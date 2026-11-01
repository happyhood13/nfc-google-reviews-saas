import { NextResponse } from "next/server";
import { createClient as createBrowserStyleClient } from "@supabase/supabase-js";
import { createClient } from "@/lib/supabase/server";
import { getMercadoPagoPayment } from "@/lib/mercadopago";

export const dynamic = "force-dynamic";

export async function GET(request: Request) {
  const url = new URL(request.url);
  const appUrl = process.env.NEXT_PUBLIC_APP_URL?.replace(/\/$/, "") || url.origin;
  const paymentId = url.searchParams.get("payment_id") || url.searchParams.get("collection_id");

  if (!paymentId) {
    return NextResponse.redirect(`${appUrl}/dashboard?payment=missing`);
  }

  const auth = createClient();
  const {
    data: { user },
  } = await auth.auth.getUser();

  if (!user) {
    return NextResponse.redirect(`${appUrl}/login?redirectTo=/dashboard`);
  }

  const payment = await getMercadoPagoPayment(paymentId).catch(() => null);
  const isOwnedPayment =
    payment?.metadata?.user_id === user.id &&
    String(payment.external_reference || "") === `reviewnfc:${user.id}`;

  if (!payment || !isOwnedPayment || payment.status !== "approved") {
    return NextResponse.redirect(`${appUrl}/dashboard?payment=pending`);
  }

  const admin = createBrowserStyleClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.SUPABASE_SERVICE_ROLE_KEY!,
    { auth: { persistSession: false } }
  );

  await admin.from("payment_orders").upsert({
    provider: "mercadopago",
    provider_payment_id: String(payment.id),
    preference_id: payment.order?.id ? String(payment.order.id) : null,
    user_id: user.id,
    status: payment.status,
    amount: payment.transaction_amount,
    currency: payment.currency_id,
    raw_reference: payment.external_reference,
  }, { onConflict: "provider,provider_payment_id" });

  const { error } = await admin
    .from("profiles")
    .update({ plan: "pro", updated_at: new Date().toISOString() })
    .eq("id", user.id);

  if (error) {
    console.error("No se pudo activar el plan Pro", error);
    return NextResponse.redirect(`${appUrl}/dashboard?payment=error`);
  }

  return NextResponse.redirect(`${appUrl}/dashboard?payment=success`);
}
