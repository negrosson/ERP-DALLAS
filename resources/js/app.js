

// ==============================================================================
// PUNTO DE ENTRADA JAVASCRIPT DEL FRONTEND (Vite Pipeline)
// ==============================================================================
// Este archivo actúa como el bootstrap principal de scripts para la aplicación.
// Inicializa la reactividad del cliente mediante Alpine.js, exponiéndolo de forma
// global para que los componentes Blade y scripts en línea puedan interactuar.
// ==============================================================================

// 1. Importación del framework reactivo ligero Alpine.js desde node_modules
import Alpine from 'alpinejs';

// 2. Registro de la instancia en el objeto global `window` para acceso global en Blade
// Esto permite definir componentes con x-data o consumir Alpine desde cualquier vista
window.Alpine = Alpine;

// 3. Inicio del motor de reactividad y observador del DOM de Alpine.js
// Escanea los atributos declarativos (x-data, x-bind, x-on, x-model, etc.) al cargar el DOM
Alpine.start();

