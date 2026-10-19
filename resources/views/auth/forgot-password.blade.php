{{-- =========================================================================
     VISTA DE SOLICITUD DE RECUPERACIÓN DE CONTRASEÑA (FORGOT PASSWORD)
     Sistema: ERP Dallas
     Propósito: Formulario para solicitar el envío del enlace de restablecimiento de contraseña vía email.
     Layout: GuestLayout (layouts/guest.blade.php)
     Tecnologías: Laravel Blade, CSRF Protection, Tailwind CSS, Breeze UI Components
     ========================================================================= --}}

{{-- Contenedor bajo el layout de invitados --}}
<x-guest-layout>
    {{-- Texto informativo explicando el proceso de recuperación de acceso --}}
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Estado de la Sesión: Mensaje de confirmación cuando el enlace ha sido enviado con éxito -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Formulario POST que dispara el controlador de envío de email de recuperación --}}
    <form method="POST" action="{{ route('password.email') }}">
        {{-- Token CSRF obligatorio para seguridad en formularios web --}}
        @csrf

        <!-- Dirección de Correo Electrónico Registrada -->
        <div>
            {{-- Componente de etiqueta para el campo Correo --}}
            <x-input-label for="email" :value="__('Email')" />
            {{-- Campo de texto tipo email con autofoco y persistencia old --}}
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            {{-- Despliegue de errores si el correo no existe en la base de datos o formato inválido --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Acciones del formulario: Botón para solicitar envío del link --}}
        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
