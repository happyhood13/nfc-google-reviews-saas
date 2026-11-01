import crypto from "crypto";

export async function getMercadoPagoPayment(paymentId: string) {
  const accessToken = process.env.MERCADOPAGO_ACCESS_TOKEN;

  if (!accessToken) {
    throw new Error("Mercado Pago access token is not configured");
  }

  const response = await fetch(`https://api.mercadopago.com/v1/payments/${encodeURIComponent(paymentId)}`, {
    headers: { Authorization: `Bearer ${accessToken}` },
    cache: "no-store",
  });

  if (!response.ok) return null;
  return response.json();
}

export function verifyMercadoPagoWebhookSignature(request: Request, dataId: string) {
  const webhookSecret = process.env.MERCADOPAGO_WEBHOOK_SECRET;
  if (!webhookSecret) return process.env.NODE_ENV === "development";

  const signature = request.headers.get("x-signature") || "";
  const requestId = request.headers.get("x-request-id") || "";
  const values = Object.fromEntries(
    signature.split(";").map((part) => {
      const [key, value] = part.split("=");
      return [key?.trim(), value?.trim()];
    }).filter(([key]) => key)
  );

  const timestamp = values.ts;
  if (!timestamp || !dataId) return false;

  const manifest = `id:${dataId};request-id:${requestId};ts:${timestamp};`;
  const expected = crypto.createHmac("sha256", webhookSecret).update(manifest).digest("hex");
  const supplied = Buffer.from(values.v1 || "", "utf8");
  const calculated = Buffer.from(expected, "utf8");

  return supplied.length === calculated.length && crypto.timingSafeEqual(supplied, calculated);
}
