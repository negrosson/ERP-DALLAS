{{-- =========================================================================
     VISTA DE EDICIÓN DE PERFIL DE USUARIO (PROFILE EDIT)
     Sistema: ERP Dallas
     Propósito: Página contenedora para la gestión integral de la cuenta de usuario (información personal, cambio de contraseña y baja de cuenta).
     Layout: AppLayout (layouts/app.blade.php con Sidebar y Navbar)
     Tecnologías: Laravel Blade, Tailwind CSS, Breeze Profile Partials
     ========================================================================= --}}

{{-- Contenedor bajo el layout autenticado de la aplicación --}}
<x-app-layout>
    {{-- Slot para el encabezado superior de la página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    {{-- Envoltorio principal con padding vertical py-12 --}}
    <div class="py-12">
        {{-- Contenedor con ancho máximo max-w-7xl centrado y separación vertical entre tarjetas (space-y-6) --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Tarjeta 1: Actualización de Información de Perfil (Nombre y Correo Electrónico) -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    {{-- Inclusión del formulario parcial de actualización de datos básicos --}}
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Tarjeta 2: Actualización de Contraseña de Seguridad -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    {{-- Inclusión del formulario parcial para modificar contraseña --}}
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Tarjeta 3: Eliminación Permanente de la Cuenta de Usuario -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    {{-- Inclusión del formulario modal parcial para eliminar la cuenta --}}
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
