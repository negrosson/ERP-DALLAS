{{-- =========================================================================
     VISTA DE VERIFICACIÓN DE CORREO ELECTRÓNICO (VERIFY EMAIL)
     Sistema: ERP Dallas
     Propósito: Pantalla de aviso para que el usuario confirme su correo electrónico antes de acceder a las funciones protegidas.
     Layout: GuestLayout (layouts/guest.blade.php)
     Tecnologías: Laravel Blade, CSRF Protection, Tailwind CSS, Breeze UI Components
     ========================================================================= --}}

{{-- Contenedor bajo el layout de invitados --}}
<x-guest-layout>
    {{-- Mensaje introductorio explicando la necesidad de verificar la dirección de correo --}}
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    {{-- Notificación visual en verde cuando se acaba de reenviar el enlace de verificación --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    {{-- Fila de acciones: Reenviar enlace de verificación o Cerrar sesión --}}
    <div class="mt-4 flex items-center justify-between">
        <!-- Formulario POST para reenviar el correo de verificación -->
        <form method="POST" action="{{ route('verification.send') }}">
            {{-- Token CSRF obligatorio --}}
            @csrf

            <div>
                {{-- Botón primario de reenvío --}}
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <!-- Formulario POST para cerrar sesión y salir del flujo de verificación -->
        <form method="POST" action="{{ route('logout') }}">
            {{-- Token CSRF obligatorio --}}
            @csrf

            {{-- Botón de texto subrayado para cerrar la sesión actual --}}
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
