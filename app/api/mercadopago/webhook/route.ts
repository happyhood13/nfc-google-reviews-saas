import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import { getMercadoPagoPayment, verifyMercadoPagoWebhookSignature } from "@/lib/mercadopago";

export const dynamic = "force-dynamic";

export async function POST(request: Request) {
  const payload = await request.json().catch(() => null);
  if (!payload) return NextResponse.json({ ok: true });

  const paymentId = String(payload?.data?.id || payload?.id || "");
  if (!verifyMercadoPagoWebhookSignature(request, paymentId)) {
    return NextResponse.json({ error: "Firma inválida" }, { status: 401 });
  }

  const type = payload?.type || payload?.topic;
  if ((type !== "payment" && type !== undefined) || !paymentId) {
    return NextResponse.json({ ok: true });
  }

  const payment = await getMercadoPagoPayment(paymentId).catch(() => null);
  if (!payment || payment.status !== "approved") {
    return NextResponse.json({ ok: true });
  }

  const userId = String(payment.metadata?.user_id || "");
  const externalReference = String(payment.external_reference || "");
  if (!userId || !externalReference.startsWith(`reviewnfc:${userId}`)) {
    console.error("Pago aprobado con referencia o usuario inválido", paymentId);
    return NextResponse.json({ ok: true });
  }

  const admin = createClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.SUPABASE_SERVICE_ROLE_KEY!,
    { auth: { persistSession: false } }
  );

  await admin.from("payment_orders").upsert({
    provider: "mercadopago",
    provider_payment_id: String(payment.id),
    preference_id: payment.order?.id ? String(payment.order.id) : null,
    user_id: userId,
    status: payment.status,
    amount: payment.transaction_amount,
    currency: payment.currency_id,
    raw_reference: externalReference,
  }, { onConflict: "provider,provider_payment_id" });

  const { error } = await admin
    .from("profiles")
    .update({ plan: "pro", updated_at: new Date().toISOString() })
    .eq("id", userId);

  if (error) {
    console.error("No se pudo activar el plan Pro", error);
    return NextResponse.json({ error: "No se pudo actualizar el plan" }, { status: 500 });
  }

  return NextResponse.json({ ok: true });
}
