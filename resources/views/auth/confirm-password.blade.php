{{-- =========================================================================
     VISTA DE CONFIRMACIÓN DE CONTRASEÑA (CONFIRM PASSWORD)
     Sistema: ERP Dallas
     Propósito: Desafío de seguridad para re-autenticar al usuario antes de acceder a zonas sensibles (como eliminar cuenta o modificar configuraciones críticas).
     Layout: GuestLayout (layouts/guest.blade.php)
     Tecnologías: Laravel Blade, CSRF Protection, Tailwind CSS, Breeze UI Components
     ========================================================================= --}}

{{-- Contenedor bajo el layout de invitados --}}
<x-guest-layout>
    {{-- Mensaje de advertencia sobre la zona segura que requiere reconfirmar la contraseña --}}
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    {{-- Formulario POST que envía la contraseña ingresada a la ruta de validación 'password.confirm' --}}
    <form method="POST" action="{{ route('password.confirm') }}">
        {{-- Token CSRF obligatorio --}}
        @csrf

        <!-- Campo de Ingreso de Contraseña Actual -->
        <div>
            {{-- Etiqueta del campo contraseña --}}
            <x-input-label for="password" :value="__('Password')" />

            {{-- Input protegido de tipo password con autocomplete de contraseña actual --}}
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            {{-- Despliegue de errores en caso de contraseña incorrecta --}}
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Botón para enviar la confirmación y desbloquear la acción sensible --}}
        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
