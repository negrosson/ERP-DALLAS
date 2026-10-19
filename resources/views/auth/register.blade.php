{{-- =========================================================================
     VISTA DE REGISTRO DE NUEVO USUARIO (REGISTER)
     Sistema: ERP Dallas
     Propósito: Formulario para la creación y alta de nuevas cuentas de usuario en el sistema.
     Layout: GuestLayout (layouts/guest.blade.php)
     Tecnologías: Laravel Blade, CSRF Protection, Tailwind CSS, Breeze UI Components
     ========================================================================= --}}

{{-- Contenedor bajo el layout de invitados --}}
<x-guest-layout>
    {{-- Formulario con método POST apuntando a la ruta de registro 'register' --}}
    <form method="POST" action="{{ route('register') }}">
        {{-- Token CSRF obligatorio para validación de seguridad de la petición --}}
        @csrf

        <!-- Nombre Completo del Usuario -->
        <div>
            {{-- Componente de etiqueta para el campo Nombre --}}
            <x-input-label for="name" :value="__('Name')" />
            {{-- Input de texto estándar con autofoco y persistencia del valor anterior (old) --}}
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            {{-- Componente de despliegue de mensajes de error de validación para 'name' --}}
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Dirección de Correo Electrónico -->
        <div class="mt-4">
            {{-- Componente de etiqueta para el campo Correo Electrónico --}}
            <x-input-label for="email" :value="__('Email')" />
            {{-- Input de tipo email con autocomplete estándar para username y persistencia old --}}
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            {{-- Componente de despliegue de errores para 'email' --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña Segura -->
        <div class="mt-4">
            {{-- Etiqueta para la contraseña --}}
            <x-input-label for="password" :value="__('Password')" />

            {{-- Input protegido de contraseña con autocompletado 'new-password' --}}
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            {{-- Despliegue de errores de complejidad o longitud de clave --}}
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmación de Contraseña -->
        <div class="mt-4">
            {{-- Etiqueta para confirmar la clave --}}
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            {{-- Input de confirmación que debe coincidir exactamente con el campo 'password' --}}
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            {{-- Mensajes de error si no coinciden las contraseñas --}}
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Acciones del Formulario: Enlace para iniciar sesión y Botón de Envío --}}
        <div class="flex items-center justify-end mt-4">
            {{-- Enlace accesible para usuarios que ya poseen una cuenta activa --}}
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            {{-- Botón primario para enviar el formulario de registro --}}
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
