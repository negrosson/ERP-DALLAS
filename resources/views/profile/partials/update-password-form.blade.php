{{-- =========================================================================
     FORMULARIO DE ACTUALIZACIÓN DE CONTRASEÑA
     Sistema: ERP Dallas
     Propósito: Permite al usuario modificar su contraseña requiriendo la actual por motivos de seguridad.
     Tecnologías: Laravel Blade, HTTP PUT (@method('put')), Error Bag de validación 'updatePassword', Alpine.js
     ========================================================================= --}}

{{-- Sección semántica para cambio de credenciales --}}
<section>
    {{-- Encabezado con recomendaciones de seguridad para passwords --}}
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    {{-- Formulario con método POST emulando PUT hacia la ruta 'password.update' --}}
    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <!-- Campo: Contraseña Actual -->
        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            {{-- Input protegido de contraseña actual para autenticar la solicitud --}}
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            {{-- Errores asignados a la bolsa de errores (error bag) 'updatePassword' --}}
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <!-- Campo: Nueva Contraseña -->
        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            {{-- Input de nueva clave --}}
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <!-- Campo: Confirmación de Nueva Contraseña -->
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            {{-- Input de confirmación idéntica --}}
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Acciones del formulario: Botón Guardar y Notificación Flotante con auto-ocultamiento Alpine --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            {{-- Notificación temporal de éxito que desaparece a los 2 segundos --}}
            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
