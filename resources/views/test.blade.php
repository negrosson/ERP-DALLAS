{{-- =========================================================================
     VISTA DE PRUEBA DE RENDERIZADO (TEST)
     Sistema: ERP Dallas
     Propósito: Vista mínima para diagnóstico rápido del motor Blade y carga del layout App.
     ========================================================================= --}}
{{-- Envoltorio de la aplicación principal --}}
<x-app-layout>
    {{-- Slot de cabecera de diagnóstico --}}
    <x-slot name="header">
        <h1>Test</h1>
    </x-slot>
    {{-- Contenido de prueba --}}
    <div>Hello</div>
</x-app-layout>
