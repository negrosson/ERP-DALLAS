{{--
    =============================================================================
    ERP DALLAS - LAYOUT PARA INVITADOS Y PANTALLAS DE AUTENTICACIÓN (guest.blade.php)
    =============================================================================
    Propósito:
    Plantilla maestra para todas las vistas no autenticadas del sistema:
    - Login (Inicio de sesión)
    - Registro de nuevos usuarios
    - Recuperación y restablecimiento de contraseña
    - Verificación y confirmación de correo
    
    Características de Diseño:
    - Paleta Dark Cyber-Executive (fondo `bg-slate-950`).
    - Destellos ambientales flotantes animados (`animate-float`, `animate-float-delayed`).
    - Glassmorphism con bordes translúcidos y desenfoque `backdrop-blur`.
    - Optimizaciones responsivas críticas para dispositivos móviles (`.field-icon`, `.auth-card`, `.btn-submit`).
    =============================================================================
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        {{-- Viewport adaptable con bloqueo de zoom descontrolado en móviles --}}
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        {{-- Token CSRF para peticiones POST seguras --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Tipografía Figtree / Outfit desde CDN Bunny -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Carga de Hojas de Estilo y Scripts compilados con Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            /* Animación de flotación suave para orbes de luz decorativos */
            @keyframes float {
                0%, 100% { transform: translateY(0) scale(1); opacity: 0.5; }
                50% { transform: translateY(-20px) scale(1.1); opacity: 0.8; }
            }
            /* Animación desfasada para el segundo orbe */
            @keyframes float-delayed {
                0%, 100% { transform: translateY(0) scale(1); opacity: 0.5; }
                50% { transform: translateY(20px) scale(1.1); opacity: 0.8; }
            }
            /* Animación de entrada con deslizamiento hacia arriba y desvanecimiento */
            @keyframes slideUpFade {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-float { animation: float 8s ease-in-out infinite; }
            .animate-float-delayed { animation: float-delayed 8s ease-in-out infinite 4s; }
            .animate-slide-up { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

            /* ── CORRECCIONES CRÍTICAS MÓVIL ── */
            /* 1) Evita que los iconos SVG dentro de los campos crezcan de forma desproporcionada */
            .field-icon {
                position: absolute;
                top: 0; bottom: 0; left: 0;
                padding-left: 0.75rem;
                display: flex;
                align-items: center;
                pointer-events: none;
                color: #64748b;
            }
            .field-icon svg {
                width: 1.25rem !important;
                height: 1.25rem !important;
                flex-shrink: 0 !important;
                display: block;
            }
            /* 2) Tarjeta de autenticación con fondo Glassmorphism y bordes sutiles */
            .auth-card {
                width: 100%;
                max-width: 100vw;
                padding: 2rem 1.25rem;
                border-radius: 0;
                background: rgba(15,23,42,0.6);
                backdrop-filter: blur(20px);
                box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
                border: 1px solid rgba(51,65,85,0.5);
                position: relative;
                overflow: hidden;
            }
            @media (min-width: 640px) {
                .auth-card {
                    max-width: 28rem;
                    padding: 2.5rem 2rem;
                    border-radius: 1rem;
                }
            }
            /* 3) Botón submit con degradado vibrante índigo-púrpura y sombra proyectada */
            .btn-submit {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0.875rem 1rem;
                border: none;
                border-radius: 0.75rem;
                font-weight: 600;
                font-size: 0.9rem;
                color: white;
                background: linear-gradient(to right, #4f46e5, #6366f1);
                box-shadow: 0 0 20px rgba(79,70,229,0.3);
                transition: all 0.3s;
                cursor: pointer;
            }
            .btn-submit:hover {
                background: linear-gradient(to right, #6366f1, #818cf8);
                box-shadow: 0 0 30px rgba(79,70,229,0.5);
                transform: translateY(-1px);
            }
            .btn-submit svg {
                width: 1rem !important;
                height: 1rem !important;
                margin-left: 0.5rem;
                flex-shrink: 0;
            }
        </style>
    </head>
    <body class="font-sans text-slate-200 antialiased bg-slate-950 selection:bg-indigo-500/30">
        
        {{-- Fondo degradado radial oscuro que genera profundidad visual --}}
        <div class="fixed inset-0 z-[-1]" style="background:radial-gradient(ellipse at top right, rgba(67,56,202,0.2), #020617, #020617)"></div>

        <div style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:1.5rem 0;">
            
            {{-- Logotipo y marca institucional --}}
            <div style="margin-bottom:1.5rem;text-align:center;">
                <a href="/" style="display:flex;flex-direction:column;align-items:center;gap:0.75rem;text-decoration:none;">
                    <div style="width:3.5rem;height:3.5rem;background:linear-gradient(135deg,#6366f1,#9333ea);border-radius:1rem;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(99,102,241,0.3);">
                        <svg style="width:1.75rem;height:1.75rem;color:white;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <span style="font-size:1.75rem;font-weight:900;background:linear-gradient(to right,#818cf8,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                        Control ERP
                    </span>
                </a>
            </div>

            {{-- Tarjeta contenedora con destellos lumínicos ambientales --}}
            <div class="auth-card animate-slide-up">
                {{-- Destellos decorativos con desenfoque extremo --}}
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl animate-float"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/20 rounded-full blur-3xl animate-float-delayed"></div>
                
                {{-- Inyección del contenido de la vista hija ($slot) --}}
                <div style="position:relative;z-index:10;">
                    {{ $slot }}
                </div>
            </div>
            
            {{-- Pie de página con copyright --}}
            <div style="margin-top:1.5rem;font-size:0.8rem;color:#475569;text-align:center;padding:0 1rem;">
                &copy; {{ date('Y') }} Control ERP. Todos los derechos reservados.
            </div>
        </div>
    </body>
</html>

