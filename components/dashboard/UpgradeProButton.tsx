"use client";

import { useState } from "react";

interface UpgradeProButtonProps {
  className?: string;
  children?: React.ReactNode;
}

export default function UpgradeProButton({ className = "", children }: UpgradeProButtonProps) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  async function startCheckout() {
    setLoading(true);
    setError("");

    try {
      const response = await fetch("/api/mercadopago/create-preference", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
      });
      const result = await response.json();

      if (!response.ok || !result.checkoutUrl) {
        throw new Error(result.error || "No se pudo iniciar el pago");
      }

      window.location.assign(result.checkoutUrl);
    } catch (checkoutError: any) {
      setError(checkoutError.message || "No se pudo iniciar el pago");
      setLoading(false);
    }
  }

  return (
    <div>
      <button type="button" onClick={startCheckout} disabled={loading} className={className}>
        {loading ? "Abriendo Mercado Pago..." : children || "Actualizar a Pro ($9.99/mes)"}
      </button>
      {error && <p className="mt-2 text-xs text-rose-600" role="alert">{error}</p>}
    </div>
  );
}
