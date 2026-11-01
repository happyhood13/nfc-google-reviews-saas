-- ==============================================================================
-- SaaS NFC & QR Reviews - Esquema de Base de Datos para Supabase (PostgreSQL 15+)
-- ==============================================================================
-- Incluye: Tablas, Relaciones, Triggers automáticos, Row Level Security (RLS),
-- Almacenamiento (Bucket logos) y Función RPC para Redirecciones y Conteo Atómico.
-- ==============================================================================

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Tabla de Perfiles de Usuario (Mapeada 1:1 con auth.users de Supabase)
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    email TEXT NOT NULL,
    full_name TEXT,
    plan TEXT NOT NULL DEFAULT 'free' CHECK (plan IN ('free', 'pro')),
    stripe_customer_id TEXT,
    stripe_subscription_id TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT timezone('utc'::text, now()),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT timezone('utc'::text, now())
);

-- 2. Tabla de Negocios y Tarjetas NFC
CREATE TABLE IF NOT EXISTS public.businesses (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    logo_url TEXT,
    google_maps_url TEXT NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    total_scans INT NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT timezone('utc'::text, now()),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT timezone('utc'::text, now())
);

-- Índices de optimización para búsquedas y slugs de redirección
CREATE INDEX IF NOT EXISTS idx_businesses_user_id ON public.businesses(user_id);
CREATE INDEX IF NOT EXISTS idx_businesses_slug ON public.businesses(slug);

-- Catálogo de planes. Los precios se mantienen fuera del cliente para evitar manipulación.
CREATE TABLE IF NOT EXISTS public.plans (
    code TEXT PRIMARY KEY CHECK (code IN ('free', 'pro')),
    name TEXT NOT NULL,
    max_businesses INTEGER,
    price_monthly NUMERIC(10, 2) NOT NULL DEFAULT 0
);

INSERT INTO public.plans (code, name, max_businesses, price_monthly)
VALUES
    ('free', 'Free', 1, 0),
    ('pro', 'Pro', NULL, 9.99)
ON CONFLICT (code) DO UPDATE SET
    name = EXCLUDED.name,
    max_businesses = EXCLUDED.max_businesses,
    price_monthly = EXCLUDED.price_monthly;

-- 3. Trigger Automático: Crear Perfil al registrarse un usuario en Supabase Auth
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO public.profiles (id, email, full_name, plan)
    VALUES (
        NEW.id,
        NEW.email,
        COALESCE(NEW.raw_user_meta_data->>'full_name', split_part(NEW.email, '@', 1)),
        'free'
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- 4. Habilitar y Configurar Row Level Security (RLS) Estricto
-- La cuota se valida también en la base de datos para impedir eludir el límite desde el cliente.
CREATE OR REPLACE FUNCTION public.enforce_business_limit()
RETURNS TRIGGER AS $$
DECLARE
    current_plan TEXT;
    current_limit INTEGER;
    current_count INTEGER;
BEGIN
    IF NEW.user_id IS DISTINCT FROM auth.uid() THEN
        RAISE EXCEPTION 'No puedes crear comercios para otro usuario';
    END IF;

    SELECT plan INTO current_plan FROM public.profiles WHERE id = NEW.user_id;
    SELECT max_businesses INTO current_limit FROM public.plans WHERE code = current_plan;

    IF current_limit IS NOT NULL THEN
        SELECT count(*) INTO current_count FROM public.businesses WHERE user_id = NEW.user_id;
        IF current_count >= current_limit THEN
            RAISE EXCEPTION 'La cuota del plan % ha sido alcanzada', current_plan;
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER SET search_path = public;

DROP TRIGGER IF EXISTS enforce_business_limit_trigger ON public.businesses;
CREATE TRIGGER enforce_business_limit_trigger
    BEFORE INSERT ON public.businesses
    FOR EACH ROW EXECUTE FUNCTION public.enforce_business_limit();

ALTER TABLE public.plans ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.businesses ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Planes visibles públicamente" ON public.plans;
CREATE POLICY "Planes visibles públicamente"
    ON public.plans FOR SELECT
    USING (true);

-- Políticas RLS para Profiles
DROP POLICY IF EXISTS "Los usuarios pueden ver su propio perfil" ON public.profiles;
CREATE POLICY "Los usuarios pueden ver su propio perfil" 
    ON public.profiles FOR SELECT 
    USING (auth.uid() = id);

DROP POLICY IF EXISTS "Los usuarios pueden actualizar su propio perfil" ON public.profiles;
CREATE POLICY "Los usuarios pueden actualizar su propio perfil"
    ON public.profiles FOR UPDATE
    USING (auth.uid() = id)
    WITH CHECK (
        auth.uid() = id
        AND plan = (SELECT p.plan FROM public.profiles p WHERE p.id = auth.uid())
        AND stripe_customer_id IS NOT DISTINCT FROM (SELECT p.stripe_customer_id FROM public.profiles p WHERE p.id = auth.uid())
        AND stripe_subscription_id IS NOT DISTINCT FROM (SELECT p.stripe_subscription_id FROM public.profiles p WHERE p.id = auth.uid())
    );

-- Políticas RLS para Negocios (Aislamiento Multi-inquilino)
DROP POLICY IF EXISTS "Los usuarios pueden ver sus propios negocios" ON public.businesses;
CREATE POLICY "Los usuarios pueden ver sus propios negocios" 
    ON public.businesses FOR SELECT 
    USING (auth.uid() = user_id);

DROP POLICY IF EXISTS "Los usuarios pueden crear negocios en su cuenta" ON public.businesses;
CREATE POLICY "Los usuarios pueden crear negocios en su cuenta" 
    ON public.businesses FOR INSERT 
    WITH CHECK (auth.uid() = user_id);

DROP POLICY IF EXISTS "Los usuarios pueden actualizar sus propios negocios" ON public.businesses;
CREATE POLICY "Los usuarios pueden actualizar sus propios negocios" 
    ON public.businesses FOR UPDATE 
    USING (auth.uid() = user_id);

DROP POLICY IF EXISTS "Los usuarios pueden eliminar sus propios negocios" ON public.businesses;
CREATE POLICY "Los usuarios pueden eliminar sus propios negocios" 
    ON public.businesses FOR DELETE 
    USING (auth.uid() = user_id);

-- 5. Procedimiento Almacenado / Función RPC para Taps NFC y Redirección Pública
CREATE OR REPLACE FUNCTION public.record_nfc_scan(target_slug TEXT)
RETURNS TABLE (
    business_id UUID,
    redirect_url TEXT,
    business_name VARCHAR,
    business_status TEXT
) AS $$
DECLARE
    found_business public.businesses%ROWTYPE;
BEGIN
    SELECT * INTO found_business
    FROM public.businesses
    WHERE slug = target_slug AND status = 'active'
    LIMIT 1;

    IF FOUND THEN
        -- Incrementar contador atómicamente
        UPDATE public.businesses
        SET total_scans = total_scans + 1,
            updated_at = timezone('utc'::text, now())
        WHERE id = found_business.id;

        RETURN QUERY SELECT 
            found_business.id,
            found_business.google_maps_url,
            found_business.name,
            found_business.status;
    ELSE
        RETURN;
    END IF;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 6. Configuración de Supabase Storage (Bucket 'logos')
INSERT INTO storage.buckets (id, name, public) 
VALUES ('logos', 'logos', true)
ON CONFLICT (id) DO NOTHING;

DROP POLICY IF EXISTS "Cualquiera puede ver logotipos públicos" ON storage.objects;
CREATE POLICY "Cualquiera puede ver logotipos públicos" 
    ON storage.objects FOR SELECT 
    USING (bucket_id = 'logos');

DROP POLICY IF EXISTS "Usuarios autenticados pueden subir logos a su carpeta" ON storage.objects;
CREATE POLICY "Usuarios autenticados pueden subir logos a su carpeta" 
    ON storage.objects FOR INSERT 
    WITH CHECK (
        bucket_id = 'logos' 
        AND auth.role() = 'authenticated'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );

DROP POLICY IF EXISTS "Usuarios autenticados pueden actualizar sus logos" ON storage.objects;
CREATE POLICY "Usuarios autenticados pueden actualizar sus logos"
    ON storage.objects FOR UPDATE
    USING (
        bucket_id = 'logos'
        AND auth.role() = 'authenticated'
        AND (storage.foldername(name))[1] = auth.uid()::text
    )
    WITH CHECK (
        bucket_id = 'logos'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );

DROP POLICY IF EXISTS "Usuarios autenticados pueden eliminar sus logos" ON storage.objects;
CREATE POLICY "Usuarios autenticados pueden eliminar sus logos"
    ON storage.objects FOR DELETE
    USING (
        bucket_id = 'logos'
        AND auth.role() = 'authenticated'
        AND (storage.foldername(name))[1] = auth.uid()::text
    );
