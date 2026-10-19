{{-- =========================================================================
     FORMULARIO MODAL DE ELIMINACIÓN DE CUENTA DE USUARIO
     Sistema: ERP Dallas
     Propósito: Permite al usuario dar de baja definitivamente su cuenta tras confirmar su contraseña en un modal emergente.
     Tecnologías: Laravel Blade, HTTP DELETE (@method('delete')), Alpine.js ($dispatch('open-modal')), Error Bag 'userDeletion'
     ========================================================================= --}}

{{-- Sección con espacio vertical entre elementos --}}
<section class="space-y-6">
    {{-- Encabezado de advertencia crítica sobre la pérdida irrevocable de datos --}}
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    {{-- Botón de acción peligrosa (rojo) que despacha el evento Alpine para abrir el modal 'confirm-user-deletion' --}}
    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    {{-- Componente Modal de confirmación: Permanece abierto si existen errores en el error bag 'userDeletion' --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        {{-- Formulario POST que emula método DELETE dirigido a 'profile.destroy' --}}
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            {{-- Título confirmatorio dentro del modal --}}
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            {{-- Explicación del requerimiento de la contraseña como factor de confirmación --}}
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <!-- Campo de Contraseña para confirmación de baja -->
            <div class="mt-6">
                {{-- Etiqueta oculta visualmente pero disponible para lectores de pantalla (sr-only) --}}
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                {{-- Input protegido de contraseña para autenticar la eliminación definitiva --}}
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                {{-- Despliegue de errores de validación de contraseña en el error bag 'userDeletion' --}}
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            {{-- Botones del Modal: Cancelar o Confirmar Eliminación --}}
            <div class="mt-6 flex justify-end">
                {{-- Botón secundario para cerrar el modal mediante evento '$dispatch('close')' --}}
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                {{-- Botón de peligro final para enviar la solicitud de borrado permanente --}}
                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
