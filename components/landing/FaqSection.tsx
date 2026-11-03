"use client";

import { ChevronDown } from "lucide-react";
import { useState } from "react";

const faqs = [
  {
    question: "¿Qué es la tecnología NFC?",
    answer: "NFC significa Near Field Communication o comunicación de campo cercano. Es una tecnología de proximidad incluida en muchos teléfonos y tarjetas inteligentes. Al acercar el dispositivo compatible, puede abrir automáticamente una dirección web previamente configurada.",
  },
  {
    question: "¿Qué pasa si el teléfono del cliente no tiene NFC?",
    answer: "No pasa nada: cada tarjeta o soporte puede llevar impreso el código QR asociado. El cliente lo escanea con la cámara de su teléfono y llega al mismo enlace que abriría una tarjeta NFC.",
  },
  {
    question: "¿Se necesita instalar una aplicación?",
    answer: "Normalmente no. Los teléfonos compatibles abren el enlace NFC desde el lector integrado y casi cualquier smartphone moderno permite escanear códigos QR con la cámara. Después se usa el navegador habitual para abrir Google Maps.",
  },
  {
    question: "¿Cómo se vincula el negocio con la tarjeta?",
    answer: "Desde el panel se introduce el enlace directo al perfil o al formulario de reseñas de Google Maps. La plataforma genera una URL dinámica y un QR. Esa dirección puede grabarse en el chip NFC o usarla como respaldo del código impreso.",
  },
  {
    question: "¿Puedo cambiar el enlace sin volver a imprimir las tarjetas?",
    answer: "Sí. La tarjeta apunta a una URL corta administrada por la plataforma y esta redirección puede actualizarse a otro enlace de Google Maps. Así, no es necesario reemplazar ni reprogramar soportes impresos para pequeños cambios de destino.",
  },
  {
    question: "¿Las reseñas pueden solicitarse mediante incentivo?",
    answer: "Se deben seguir las políticas de Google y obtener opiniones genuinas. Una empresa puede solicitar una opinión, pero no debe exigir una valoración positiva, comprar reseñas ni recompensar únicamente a clientes que publiquen comentarios favorables.",
  },
  {
    question: "¿Qué teléfonos son compatibles con NFC?",
    answer: "La mayoría de teléfonos Android modernos y los modelos recientes de iPhone permiten NFC. Puede ser necesario activar NFC en la configuración o acercar el dispositivo durante unos segundos al lector. La compatibilidad exacta varía según la marca y el sistema operativo.",
  },
  {
    question: "¿La solución sirve para varias sucursales?",
    answer: "Sí. Cada negocio o sucursal puede tener su propio enlace de Google Maps, código QR, tarjeta NFC y estadísticas. El plan Free permite comenzar con una tarjeta y el plan Pro está pensado para operaciones con varias ubicaciones.",
  },
];

export default function FaqSection() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const structuredData = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: faqs.map((faq) => ({
      "@type": "Question",
      name: faq.question,
      acceptedAnswer: {
        "@type": "Answer",
        text: faq.answer,
      },
    })),
  };

  return (
    <section className="bg-slate-50 py-20">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }} />
      <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <header className="mb-12 text-center">
          <span className="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Centro de ayuda</span>
          <h2 className="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
            Preguntas frecuentes sobre NFC y códigos QR
          </h2>
          <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600">
            Resuelve las dudas principales antes de implementar tarjetas de reseñas en tu negocio, sin aplicaciones adicionales ni procesos técnicos complejos.
          </p>
        </header>

        <div className="space-y-3">
          {faqs.map((faq, index) => {
            const isOpen = openIndex === index;
            return (
              <div key={faq.question} className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <h3>
                  <button
                    type="button"
                    className="flex w-full items-center justify-between gap-5 px-5 py-5 text-left text-sm font-bold text-slate-900 sm:px-6"
                    aria-expanded={isOpen}
                    aria-controls={`faq-panel-${index}`}
                    onClick={() => setOpenIndex(isOpen ? null : index)}
                  >
                    {faq.question}
                    <ChevronDown className={`h-5 w-5 shrink-0 text-brand-600 transition-transform ${isOpen ? "rotate-180" : ""}`} />
                  </button>
                </h3>
                <div
                  id={`faq-panel-${index}`}
                  hidden={!isOpen}
                  className="border-t border-slate-100 px-5 py-5 text-sm leading-7 text-slate-600 sm:px-6"
                >
                  {faq.answer}
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
