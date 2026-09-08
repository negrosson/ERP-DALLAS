<x-guest-layout>
    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        {{-- Encabezado --}}
        <div class="text-center mb-8">
            <h2 style="font-size:1.4rem;font-weight:700;color:white;margin-bottom:0.4rem;">Bienvenido de nuevo</h2>
            <p style="font-size:0.875rem;color:#94a3b8;">Ingresa tus credenciales para continuar</p>
        </div>

        {{-- Correo Electrónico --}}
        <div>
            <label for="email" style="display:block;font-size:0.875rem;font-weight:500;color:#cbd5e1;margin-bottom:0.35rem;">
                Correo Electrónico
            </label>
            <div style="position:relative;">
                {{-- Icono email con tamaño fijo --}}
                <div class="field-icon">
                    <svg style="width:20px;height:20px;flex-shrink:0;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="ejemplo@empresa.com"
                    style="display:block;width:100%;padding:0.65rem 0.75rem 0.65rem 2.75rem;border:1px solid rgba(71,85,105,0.5);border-radius:0.75rem;background:rgba(15,23,42,0.5);color:#e2e8f0;font-size:0.95rem;outline:none;box-sizing:border-box;-webkit-appearance:none;"
                >
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400" />
        </div>

        {{-- Contraseña --}}
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.35rem;">
                <label for="password" style="font-size:0.875rem;font-weight:500;color:#cbd5e1;">Contraseña</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size:0.75rem;font-weight:500;color:#818cf8;text-decoration:none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <div style="position:relative;">
                {{-- Icono candado con tamaño fijo --}}
                <div class="field-icon">
                    <svg style="width:20px;height:20px;flex-shrink:0;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="display:block;width:100%;padding:0.65rem 0.75rem 0.65rem 2.75rem;border:1px solid rgba(71,85,105,0.5);border-radius:0.75rem;background:rgba(15,23,42,0.5);color:#e2e8f0;font-size:0.95rem;outline:none;box-sizing:border-box;-webkit-appearance:none;"
                >
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400" />
        </div>

        {{-- Recordar sesión --}}
        <div style="display:flex;align-items:center;">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                style="width:1rem;height:1rem;border:1px solid #475569;border-radius:0.25rem;background:#0f172a;accent-color:#6366f1;"
            >
            <label for="remember_me" style="margin-left:0.5rem;font-size:0.875rem;color:#94a3b8;">
                Recordar sesión
            </label>
        </div>

        {{-- Botón Submit --}}
        <div style="padding-top:0.5rem;">
            <button type="submit" class="btn-submit">
                <span>Ingresar al Sistema</span>
                <svg style="width:16px;height:16px;margin-left:0.5rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </form>
</x-guest-layout>
