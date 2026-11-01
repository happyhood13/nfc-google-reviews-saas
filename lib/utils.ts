import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/**
 * Valida de forma estricta que la URL pertenezca a los dominios oficiales de Google Maps
 */
export function isValidGoogleMapsUrl(url: string): boolean {
  if (!url || typeof url !== "string") return false;
  const trimmed = url.trim();

  try {
    const parsed = new URL(trimmed);
    const host = parsed.hostname.toLowerCase();
    const path = parsed.pathname.toLowerCase();

    // Enlaces de compartir directo de la app móvil: maps.app.goo.gl
    if (host === "maps.app.goo.gl") return true;

    // Enlaces cortos clásicos: goo.gl/maps
    if (host === "goo.gl" && path.startsWith("/maps")) return true;

    // Enlace directo de reseñas de Google Local: search.google.com/local/writereview
    if (host === "search.google.com" && (path.startsWith("/local/writereview") || path.startsWith("/local/reviews"))) {
      return true;
    }

    // Subdominios maps.google.com o maps.google.es, etc.
    if (/^maps\.google\.(com(\.[a-z]{2})?|[a-z]{2})$/.test(host)) return true;

    // Dominio principal de google con ruta /maps
    if (/^(www\.)?google\.(com(\.[a-z]{2})?|[a-z]{2})$/.test(host) && (path.startsWith("/maps") || path.startsWith("/search"))) {
      return true;
    }

    return false;
  } catch {
    return false;
  }
}

/**
 * Genera un slug seguro a partir del nombre del negocio
 */
export function generateSlug(text: string): string {
  return text
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .substring(0, 50);
}
