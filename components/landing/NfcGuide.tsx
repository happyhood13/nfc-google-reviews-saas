import { Building2, Coffee, Hotel, Palette, Smartphone, Star } from "lucide-react";

const steps = [
  {
    number: "01",
    title: "Crea el perfil de tu negocio",
    text: "Introduce el nombre comercial, teléfono, logotipo y el enlace directo al apartado de reseñas de Google Maps. La plataforma genera una URL corta que podrás mantener aunque cambien tus datos.",
  },
  {
    number: "02",
    title: "Genera tu QR y programa el dispositivo NFC",
    text: "Descarga el código QR vectorial e imprímelo en tarjetas, mesas, mostradores o pegatinas. Para usar NFC, programamos la misma URL en una tarjeta o pegatina compatible.",
  },
  {
    number: "03",
    title: "Colócalo en el punto de atención",
    text: "Sitúa el soporte cerca del momento de pago, al entregar el pedido o cuando finalice una estancia. Una llamada clara, como “Déjanos tu opinión en Google”, facilita la acción.",
  },
  {
    number: "04",
    title: "El cliente abre Google Maps",
    text: "Al escanear el QR o acercar el teléfono al chip, el cliente accede al perfil del negocio y puede publicar una reseña sin buscar la empresa manualmente.",
  },
];

const sectors = [
  {
    icon: Coffee,
    title: "Restaurantes y cafeterías",
    text: "Invita a opinar justo después de una experiencia agradable. Una reseña reciente puede ayudar a otros comensales a elegir mesa con más confianza.",
  },
  {
    icon: Hotel,
    title: "Hoteles y alojamientos",
    text: "Recoge valoraciones al finalizar la estancia, en la recepción o mediante una etiqueta colocada discretamente junto a la salida.",
  },
  {
    icon: Palette,
    title: "Estéticas y centros de bienestar",
    text: "Las fotos y testimonios de clientes realzan la confianza. Colocar el acceso de reseñas junto al mostrador ayuda a solicitar opiniones sin interrumpir la atención.",
  },
  {
    icon: Building2,
    title: "Otros negocios locales",
    text: "Tiendas, clínicas, talleres y profesionales pueden transformar una visita satisfactoria en evidencia pública y mejorar su presencia en búsquedas locales.",
  },
];

export default function NfcGuide() {
  return (
    <>
      <section className="py-20 bg-white border-y border-slate-200/80">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <header className="mx-auto mb-14 max-w-3xl text-center">
            <span className="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">
              Guía práctica
            </span>
            <h2 className="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
              Cómo las tarjetas NFC pueden mejorar la reputación local
            </h2>
            <p className="mt-4 text-sm leading-7 text-slate-600 sm:text-base">
              NFC y QR convierten una interacción presencial en una reseña sencilla de publicar. El objetivo no es presionar al cliente, sino eliminar pasos innecesarios y facilitar una opinión honesta.
            </p>
          </header>

          <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            {steps.map((step) => (
              <article key={step.number} className="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <span className="text-xs font-bold tracking-widest text-brand-600">{step.number}</span>
                <h3 className="mt-3 text-base font-bold text-slate-900">{step.title}</h3>
                <p className="mt-3 text-sm leading-6 text-slate-600">{step.text}</p>
              </article>
            ))}
          </div>

          <div className="mt-16 grid gap-10 lg:grid-cols-[1fr_1fr] lg:items-start">
            <article>
              <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <Star className="h-6 w-6 fill-current" />
              </div>
              <h3 className="font-display text-2xl font-extrabold text-slate-900">
                Por qué importan las reseñas de cinco estrellas
              </h3>
              <div className="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                <p>
                  Google Maps ofrece información muy influyente antes de una compra o visita. La puntuación visible, la cantidad de reseñas y las opiniones recientes ayudan a comparar negocios cercanos y a anticipar la calidad del servicio.
                </p>
                <p>
                  Un flujo constante de reseñas auténticas puede fortalecer la confianza y aportar contenido actualizado sobre horarios, trato, limpieza, producto y atención. Esto también proporciona señales útiles para la estrategia local del negocio.
                </p>
                <p>
                  Las reseñas deben solicitarse de forma transparente y con el mismo respeto para clientes satisfechos o insatisfechos. No se deben comprar opiniones, filtrar a las personas por su resultado ni ofrecer incentivos que condicionen el contenido.
                </p>
              </div>
            </article>

            <article className="rounded-3xl border border-brand-100 bg-brand-50/60 p-6 sm:p-8">
              <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-white text-brand-600 shadow-sm">
                <Smartphone className="h-6 w-6" />
              </div>
              <h3 className="font-display text-2xl font-extrabold text-slate-900">
                Beneficios para cada tipo de negocio
              </h3>
              <div className="mt-6 space-y-5">
                {sectors.map(({ icon: Icon, title, text }) => (
                  <div key={title} className="flex gap-4">
                    <div className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                      <Icon className="h-4 w-4" />
                    </div>
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">{title}</h4>
                      <p className="mt-1 text-sm leading-6 text-slate-600">{text}</p>
                    </div>
                  </div>
                ))}
              </div>
            </article>
          </div>
        </div>
      </section>
    </>
  );
}
