{{-- =========================================================================
     VISTA DE RESTABLECIMIENTO DE CONTRASEÑA (RESET PASSWORD)
     Sistema: ERP Dallas
     Propósito: Formulario para ingresar una nueva contraseña validando el token de seguridad firmado enviado por correo.
     Layout: GuestLayout (layouts/guest.blade.php)
     Tecnologías: Laravel Blade, CSRF Protection, Tailwind CSS, Breeze UI Components
     ========================================================================= --}}

{{-- Contenedor bajo el layout de invitados --}}
<x-guest-layout>
    {{-- Formulario POST que envía el token y la nueva contraseña al controlador NewPasswordController --}}
    <form method="POST" action="{{ route('password.store') }}">
        {{-- Token CSRF obligatorio para seguridad del formulario --}}
        @csrf

        <!-- Token de Seguridad de Restablecimiento (Obtenido desde la ruta con firma criptográfica) -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Dirección de Correo Electrónico del Usuario -->
        <div>
            {{-- Etiqueta para el email --}}
            <x-input-label for="email" :value="__('Email')" />
            {{-- Input de texto precargado con el email proveniente del request --}}
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            {{-- Despliegue de errores de validación de correo --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Nueva Contraseña -->
        <div class="mt-4">
            {{-- Etiqueta para la nueva contraseña --}}
            <x-input-label for="password" :value="__('Password')" />
            {{-- Input protegido para la nueva contraseña requerida --}}
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            {{-- Despliegue de errores de longitud o complejidad --}}
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmación de la Nueva Contraseña -->
        <div class="mt-4">
            {{-- Etiqueta de confirmación --}}
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            {{-- Input protegido de confirmación que debe ser idéntico al campo 'password' --}}
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            {{-- Mensaje de error si la confirmación no coincide --}}
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Acciones del formulario: Botón para confirmar y guardar la nueva contraseña --}}
        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
