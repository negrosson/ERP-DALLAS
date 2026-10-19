{{-- =========================================================================
     VISTA DE INICIO DE SESIÓN (LOGIN)
     Sistema: ERP Dallas
     Propósito: Formulario de autenticación para acceso seguro de operadores y administradores.
     Layout: GuestLayout (layouts/guest.blade.php con fondo degradado y panel flotante)
     Tecnologías: Laravel Blade, CSRF Protection, Tailwind CSS / Inline Styles adaptativos
     ========================================================================= --}}

{{-- Contenedor bajo el layout de invitados --}}
<x-guest-layout>
    {{-- Mensaje de estado de sesión (por ejemplo: si se envió link de restablecimiento o expiró sesión) --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Formulario de autenticación con método POST apuntando a la ruta 'login' --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        {{-- Token de seguridad CSRF obligatorio para prevenir ataques de falsificación de peticiones en sitios cruzados --}}
        @csrf

        {{-- Encabezado del Formulario de Inicio de Sesión --}}
        <div class="text-center mb-8">
            {{-- Título principal en blanco de alto contraste --}}
            <h2 style="font-size:1.4rem;font-weight:700;color:white;margin-bottom:0.4rem;">Bienvenido de nuevo</h2>
            {{-- Subtítulo explicativo en tono slate suave --}}
            <p style="font-size:0.875rem;color:#94a3b8;">Ingresa tus credenciales para continuar</p>
        </div>

        {{-- Campo de Ingreso de Correo Electrónico --}}
        <div>
            {{-- Etiqueta descriptiva accesible asociada al ID del input --}}
            <label for="email" style="display:block;font-size:0.875rem;font-weight:500;color:#cbd5e1;margin-bottom:0.35rem;">
                Correo Electrónico
            </label>
            {{-- Envoltorio relativo para posicionamiento del icono dentro del input --}}
            <div style="position:relative;">
                {{-- Icono visual SVG de sobre de correo posicionado a la izquierda --}}
                <div class="field-icon">
                    <svg style="width:20px;height:20px;flex-shrink:0;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
                {{-- Campo de texto de tipo email con preservación del valor anterior (old) y autofoco --}}
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="ejemplo@empresa.com"
                    style="display:block;width:100%;padding:0.65rem 0.75rem 0.65rem 2.75rem;border:1px solid rgba(71,85,105,0.5);border-radius:0.75rem;background:rgba(15,23,42,0.5);color:#e2e8f0;font-size:0.95rem;outline:none;box-sizing:border-box;-webkit-appearance:none;"
                >
            </div>
            {{-- Mensaje de error de validación de correo electrónico --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400" />
        </div>

        {{-- Campo de Ingreso de Contraseña --}}
        <div>
            {{-- Cabecera del campo con etiqueta y enlace de recuperación de contraseña --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.35rem;">
                <label for="password" style="font-size:0.875rem;font-weight:500;color:#cbd5e1;">Contraseña</label>
                {{-- Enlace a solicitud de restablecimiento si la ruta está activa en el sistema --}}
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size:0.75rem;font-weight:500;color:#818cf8;text-decoration:none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            {{-- Envoltorio relativo para icono y campo de clave oculta --}}
            <div style="position:relative;">
                {{-- Icono visual SVG de candado de seguridad --}}
                <div class="field-icon">
                    <svg style="width:20px;height:20px;flex-shrink:0;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                {{-- Input protegido de tipo password --}}
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="display:block;width:100%;padding:0.65rem 0.75rem 0.65rem 2.75rem;border:1px solid rgba(71,85,105,0.5);border-radius:0.75rem;background:rgba(15,23,42,0.5);color:#e2e8f0;font-size:0.95rem;outline:none;box-sizing:border-box;-webkit-appearance:none;"
                >
            </div>
            {{-- Mensaje de error de validación de contraseña --}}
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400" />
        </div>

        {{-- Casilla de Verificación para Persistencia de Sesión (Remember Me) --}}
        <div style="display:flex;align-items:center;">
            {{-- Checkbox nativo estilizado con color de acento índigo --}}
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                style="width:1rem;height:1rem;border:1px solid #475569;border-radius:0.25rem;background:#0f172a;accent-color:#6366f1;"
            >
            {{-- Etiqueta del checkbox --}}
            <label for="remember_me" style="margin-left:0.5rem;font-size:0.875rem;color:#94a3b8;">
                Recordar sesión
            </label>
        </div>

        {{-- Botón de Envío del Formulario (Submit) --}}
        <div style="padding-top:0.5rem;">
            {{-- Botón principal con estilos de gradiente y efecto hover definidos en guest.blade.php --}}
            <button type="submit" class="btn-submit">
                <span>Ingresar al Sistema</span>
                {{-- Icono de flecha indicativa de ingreso --}}
                <svg style="width:16px;height:16px;margin-left:0.5rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </form>
</x-guest-layout>
