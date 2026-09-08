<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Bodega;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Contracts\OcrProviderInterface::class, function ($app) {
            $provider = env('OCR_PROVIDER', 'mock');

            if ($provider === 'mindee') {
                return new \App\Services\Ocr\MindeeOcrProvider();
            }

            // Fallback por defecto a la simulación
            return new \App\Services\Ocr\MockOcrProvider();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS si se accede a través de ngrok para evitar errores de mixed content (CSS roto)
        if (str_contains(request()->getHost(), 'ngrok')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // Forzar a Vite a ignorar el archivo 'hot' de desarrollo cuando se usa Ngrok,
            // de esta manera los celulares cargarán siempre los estilos de producción (npm run build).
            \Illuminate\Support\Facades\Vite::useHotFile(public_path('ngrok-ignore-hot'));
        }

        // Inyecta las bodegas activas en el sidebar para mostrar el sub-menú de bodegas
        View::composer('layouts.sidebar', function ($view) {
            if (Auth::check()) {
                $view->with('sidebarBodegas', Bodega::where('activa', true)->orderBy('nombre')->get(['id', 'nombre', 'es_refrigerada']));
            } else {
                $view->with('sidebarBodegas', collect());
            }
        });
    }
}
