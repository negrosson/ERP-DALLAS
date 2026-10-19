{{-- =========================================================================
     FORMULARIO DE ACTUALIZACIÓN DE INFORMACIÓN DE PERFIL
     Sistema: ERP Dallas
     Propósito: Permite al usuario autenticado modificar su nombre y dirección de correo electrónico, gestionando además la reverificación de email.
     Tecnologías: Laravel Blade, HTTP PATCH, Alpine.js (desvanecimiento del mensaje 'Saved.'), Tailwind CSS
     ========================================================================= --}}

{{-- Sección semántica para la edición de datos de cuenta --}}
<section>
    {{-- Cabecera de la sección con título y descripción explicativa --}}
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    {{-- Formulario oculto auxiliar para reenviar el enlace de verificación de correo sin alterar el formulario principal --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- Formulario principal con método POST que emula PATCH mediante la directiva @method --}}
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Campo: Nombre Completo -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            {{-- Input de texto con valor por defecto del usuario o el recuperado de sesión anterior --}}
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Campo: Correo Electrónico -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            {{-- Input de email con autocomplete 'username' --}}
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            {{-- Bloque condicional para usuarios que requieren verificar su correo y aún no lo han completado --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        {{-- Botón que enlaza al formulario auxiliar 'send-verification' vía atributo form --}}
                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    {{-- Mensaje de éxito al despachar un nuevo email de verificación --}}
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Acciones del formulario: Botón Guardar y Notificación Flotante con auto-ocultamiento Alpine --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            {{-- Notificación efímera con Alpine.js que desaparece suavemente tras 2 segundos (2000ms) --}}
            @if (session('status') === 'profile-updated')
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
