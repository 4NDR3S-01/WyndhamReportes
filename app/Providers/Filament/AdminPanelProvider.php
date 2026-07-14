<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Inicio;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->maxContentWidth(Width::Full)
            ->login()
            ->colors([
                'primary' => Color::hex('#0B3B60'),
            ])
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/logo_wyndham-manta.jpg'))
            ->navigationGroups([
                NavigationGroup::make('Cocina')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Medico')
                    ->collapsible()
                    ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Inicio::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Route::currentRouteName() === 'filament.admin.auth.login' ? Blade::render('<style>
                    /* ============================================================
                       LOGIN — Wyndham Manta Medical Panel v2
                       Imagen de fondo nítida + Glassmorphism ligero
                       ============================================================ */
                    html, body {
                        height: 100%;
                    }
                    body {
                        background-image:
                            linear-gradient(
                                135deg,
                                rgba(11, 59, 96, 0.30) 0%,
                                rgba(217, 112, 74, 0.08) 50%,
                                rgba(10, 15, 30, 0.35) 100%
                            ),
                            url("/images/portada.png") !important;
                        background-size: cover !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                        background-attachment: fixed !important;
                    }

                    /* Contenedor transparente para ver la imagen */
                    .fi-simple-layout {
                        background: transparent !important;
                        min-height: 100dvh;
                    }

                    /* ========== MODO OSCURO: mantener el login legible y con sus colores de marca ==========
                       El panel puede quedar en modo oscuro (localStorage) y Filament marca <html>
                       con la clase "dark", lo que volvería el texto blanco sobre la tarjeta blanca.
                       La tarjeta ya es blanca (!important) en ambos modos; aquí solo neutralizamos
                       los FONDOS oscuros de Filament y fijamos un color base legible para el texto
                       SIN estilo. Los colores de marca (título, etiquetas, botón) se definen más
                       abajo con !important y se aplican IGUAL en claro y oscuro, por eso NO se
                       sobreescriben aquí. */
                    html.dark .fi-simple-layout,
                    html.dark .fi-simple-main-ctn {
                        background: transparent !important;
                    }
                    html.dark .fi-simple-main {
                        color: #334155 !important; /* color base solo para texto sin estilo propio */
                    }
                    html.dark :where(.fi-simple-main) * {
                        background-color: transparent !important;
                    }

                    /* Animación de entrada del card */
                    @keyframes login-enter {
                        0%   { opacity: 0; transform: translateY(20px) scale(0.98); }
                        100% { opacity: 1; transform: translateY(0) scale(1); }
                    }

                    /* ========== TARJETA PRINCIPAL (glassmorphism ligero) ========== */
                    .fi-simple-main {
                        animation: login-enter 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
                        background: rgba(255, 255, 255, 0.88) !important;
                        backdrop-filter: blur(12px) !important;
                        -webkit-backdrop-filter: blur(12px) !important;
                        border-radius: 1.5rem !important;
                        padding: 2.25rem 2.25rem 2rem !important;
                        box-shadow:
                            0 20px 40px -12px rgba(0, 0, 0, 0.25),
                            0 8px 16px -6px rgba(0, 0, 0, 0.08),
                            inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
                        border: 1px solid rgba(255, 255, 255, 0.6) !important;
                        max-width: 400px !important;
                        width: 100%;
                        position: relative;
                        overflow: hidden !important; /* Evita que la barra sobresalga en las esquinas */
                    }
                    /* Barra decorativa superior con gradiente premium */
                    .fi-simple-main::before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 5px;
                        background: linear-gradient(90deg, #0B3B60 0%, #0284c7 40%, #f59e0b 80%, #D9704A 100%);
                        border-radius: 1.5rem 1.5rem 0 0;
                    }

                    /* ========== CORRECCIÓN DE CAPAS (Quitar contenedor interno por defecto de Filament) ========== */
                    .fi-simple-main section,
                    .fi-simple-main > div:not(.fi-logo),
                    .fi-simple-main form {
                        background: transparent !important;
                        box-shadow: none !important;
                        border: none !important;
                        --tw-ring-shadow: 0 0 #0000 !important;
                        --tw-ring-color: transparent !important;
                    }
                    
                    /* Ajustar padding interno de Filament para evitar doble padding */
                    .fi-simple-main > div:not(.fi-logo) {
                        padding-left: 0 !important;
                        padding-right: 0 !important;
                        padding-bottom: 0 !important;
                    }

                    /* ========== TIPOGRAFÍA ========== */
                    /* El título del login es <h1 class="fi-simple-header-heading">.
                       Color de marca fijado en AMBOS modos (no depende de .dark). */
                    .fi-simple-main h1,
                    .fi-simple-main .fi-simple-header-heading {
                        color: #0B3B60 !important;
                        font-weight: 800 !important;
                        font-size: 1.35rem !important;
                        letter-spacing: -0.02em;
                        margin-bottom: 0.25rem !important;
                    }
                    .fi-simple-main > p {
                        color: #475569 !important;
                        font-size: 0.875rem !important;
                        margin-bottom: 1.25rem !important;
                    }
                    /* Etiquetas de campo: cubre <label> y el <span> interno para
                       que el modo oscuro no las vuelva blancas. Mismo color en claro/oscuro. */
                    .fi-simple-main label,
                    .fi-simple-main .fi-fo-field-label,
                    .fi-simple-main .fi-fo-field-label-content {
                        color: #334155 !important;
                        font-weight: 600 !important;
                        font-size: 0.8125rem !important;
                    }
                    .fi-simple-main .fi-link {
                        color: #0E7490 !important;
                        font-weight: 500 !important;
                    }
                    .fi-simple-main .fi-link:hover {
                        color: #0B3B60 !important;
                    }

                    /* ========== CONTENEDOR DE INPUTS (Filament) ========== */
                    .fi-simple-main .fi-input-wrapper {
                        background: #ffffff !important;
                        border: 1.5px solid #e2e8f0 !important;
                        border-radius: 0.75rem !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease;
                        box-shadow: none !important;
                        overflow: hidden; /* Mantiene redondeados los bordes del contenido interno */
                    }
                    .fi-simple-main .fi-input-wrapper:focus-within {
                        border-color: #0E7490 !important;
                        box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.12) !important;
                    }
                    
                    /* Limpiar estilos del input interno para que herede del wrapper */
                    .fi-simple-main .fi-input-wrapper input {
                        color: #1e293b !important;
                        background: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                        font-size: 0.9375rem !important;
                    }
                    .fi-simple-main .fi-input-wrapper input:focus {
                        --tw-ring-shadow: 0 0 #0000 !important;
                        outline: none !important;
                    }
                    .fi-simple-main input::placeholder {
                        color: #94a3b8 !important;
                    }

                    /* ========== ERRORES DE VALIDACIÓN (mismo color en claro y oscuro) ==========
                       Al ingresar credenciales inválidas, los mensajes y el borde del input
                       deben verse igual sin importar el modo del panel. */
                    .fi-simple-main .fi-fo-field-wrp-error-message,
                    .fi-simple-main .fi-fo-field-wrp-error-list {
                        color: #dc2626 !important;
                    }
                    .fi-simple-main .fi-fo-field-wrp-error .fi-input-wrapper {
                        border-color: #dc2626 !important;
                        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12) !important;
                    }

                    /* ========== CHECKBOX "Recordarme" ========== */
                    .fi-simple-main input[type="checkbox"] {
                        accent-color: #0E7490 !important;
                        width: 1rem !important;
                        height: 1rem !important;
                        border-radius: 4px !important;
                        cursor: pointer;
                    }
                    .fi-simple-main input[type="checkbox"] + span,
                    .fi-simple-main label:has(input[type="checkbox"]) {
                        color: #475569 !important;
                        font-size: 0.8125rem !important;
                        font-weight: 500 !important;
                        cursor: pointer;
                        user-select: none;
                    }

                    /* ========== BOTÓN SUBMIT ========== */
                    .fi-simple-main button[type="submit"] {
                        background: linear-gradient(135deg, #0B3B60 0%, #0E7490 100%) !important;
                        border-radius: 0.75rem !important;
                        padding: 0.7rem 1.25rem !important;
                        font-weight: 700 !important;
                        font-size: 0.9375rem !important;
                        color: #ffffff !important;
                        border: none !important;
                        transition: all 0.15s ease;
                        box-shadow: 0 4px 14px rgba(11, 59, 96, 0.3) !important;
                        cursor: pointer;
                    }
                    .fi-simple-main button[type="submit"]:hover {
                        box-shadow: 0 6px 20px rgba(11, 59, 96, 0.4) !important;
                        transform: translateY(-1px);
                    }
                    .fi-simple-main button[type="submit"]:active {
                        transform: translateY(0) scale(0.98);
                    }

                    /* ========== LOGO ========== */
                    .fi-simple-main .fi-logo {
                        margin-bottom: 1.25rem !important;
                    }

                    /* ========== RESPONSIVE ========== */
                    @media (max-width: 480px) {
                        .fi-simple-main {
                            padding: 1.75rem 1.5rem !important;
                            border-radius: 1.25rem !important;
                            margin: 1rem;
                        }
                        .fi-simple-main::before {
                            left: 1rem;
                            right: 1rem;
                        }
                    }
                    @media (prefers-reduced-motion: reduce) {
                        .fi-simple-main {
                            animation: none !important;
                        }
                    }
                </style>') . <<<HTML
<script>
    (function () {
        var html = document.documentElement;

        // Solo actúa mientras la pantalla de login está visible, para NO
        // afectar el modo (claro/oscuro) que el usuario tiene guardado en el
        // resto de la aplicación tras iniciar sesión.
        var isLoginPage = function () {
            return !!document.querySelector('form[action*="auth/login"], .fi-simple-layout');
        };

        var forceLight = function () {
            if (!isLoginPage()) {
                document.removeEventListener('livewire:navigated', forceLight);
                return;
            }
            // El login siempre se muestra en modo claro/blanco.
            html.classList.remove('dark');
        };

        // 1) Inmediato: deshace la aplicación síncrona de modo oscuro que hace
        //    el layout de Filament al cargar la página.
        forceLight();

        // 2) Filament reaplica el modo oscuro en cada navegación de Livewire,
        //    incluido el montaje inicial de esta página de login. Lo neutralizamos.
        document.addEventListener('livewire:navigated', forceLight);
    })();
</script>
HTML
            : Blade::render('<style>
                    /* Ocultar el selector de temas duplicado */
                    .fi-theme-switcher {
                        display: none !important;
                    }
                    /* Ocultar el avatar/perfil del usuario */
                    .fi-topbar .fi-user-menu {
                        display: none !important;
                    }
                    /* Evita el parpadeo del modal de confirmación de cierre de sesión */
                    [x-cloak] {
                        display: none !important;
                    }
                    /* Animación de entrada del modal: entra desde abajo-derecha y se centra */
                    @keyframes modalIn {
                        from {
                            opacity: 0;
                            transform: translate(-50%, -50%) scale(0.95);
                        }
                        to {
                            opacity: 1;
                            transform: translate(-50%, -50%) scale(1);
                        }
                    }
                </style>'),
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                function (): string {
                    $fecha = now()->translatedFormat('l, d \d\e F');

                    return Blade::render('<div class="me-4 flex items-center gap-3" x-data="{ time: new Date().toLocaleTimeString(\'es-ES\', { hour: \'2-digit\', minute: \'2-digit\' }) }" x-init="setInterval(() => time = new Date().toLocaleTimeString(\'es-ES\', { hour: \'2-digit\', minute: \'2-digit\' }), 1000)">
                        <div class="flex items-center gap-3 rounded-full border-0 bg-transparent shadow-none transition-colors" style="padding:0.25rem 0.5rem;">
                            <div class="flex items-center justify-center rounded-full bg-gradient-to-br from-primary-50 to-primary-100 shadow-inner dark:from-primary-900/50 dark:to-primary-800/50" style="width:2.25rem; height:2.25rem;">
                                <x-heroicon-o-clock class="text-primary-600 dark:text-primary-400" style="width:1.25rem; height:1.25rem;" />
                            </div>
                            <div class="leading-none" style="text-align:center; padding-right:0.5rem;">
                                <p class="mb-0.5 font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400" style="font-size:0.6875rem;">' . e($fecha) . '</p>
                                <p class="font-black tracking-tight text-gray-900 dark:text-white" style="font-size:1rem;" x-text="time"></p>
                            </div>
                        </div>

                        <button type="button" x-data="{ theme: localStorage.getItem(\'theme\') || \'light\' }" x-on:click="theme = theme === \'dark\' ? \'light\' : \'dark\'; localStorage.setItem(\'theme\', theme); document.documentElement.classList.toggle(\'dark\', theme === \'dark\'); $dispatch(\'theme-changed\', theme);" class="relative flex h-10 w-10 items-center justify-center rounded-full backdrop-blur-md transition-all duration-300 hover:scale-105 focus:outline-none" x-bind:style="theme === \'dark\' ? \'background-color:#1f2937; border:2px solid #818cf8; box-shadow:0 1px 2px 0 rgba(0,0,0,0.30);\' : \'background-color:#ffffff; border:2px solid #fbbf24; box-shadow:0 1px 2px 0 rgba(0,0,0,0.10);\'" title="Alternar Modo Oscuro/Claro">
                            <div class="relative flex h-5 w-5 items-center justify-center">
                                <x-heroicon-o-moon
                                    class="absolute inset-0 h-5 w-5 transition-all duration-500 ease-in-out"
                                    x-bind:style="theme === \'dark\' ? \'opacity:1; transform:scale(1) rotate(0deg); color:#7dd3fc; filter:drop-shadow(0 0 5px rgba(125,211,252,0.85));\' : \'opacity:0; transform:scale(0.5) rotate(90deg); color:#38bdf8; filter:none;\'"
                                />
                                <x-heroicon-o-sun
                                    class="absolute inset-0 h-5 w-5 transition-all duration-500 ease-in-out"
                                    x-bind:style="theme !== \'dark\' ? \'opacity:1; transform:scale(1) rotate(0deg); color:#eab308; filter:drop-shadow(0 0 7px rgba(234,179,8,0.95));\' : \'opacity:0; transform:scale(0.5) rotate(-90deg); color:#facc15; filter:none;\'"
                                />
                            </div>
                        </button>

                        <div>
                            <x-filament::button
                                color="danger"
                                outlined="true"
                                size="sm"
                                icon="heroicon-m-arrow-right-on-rectangle"
                                type="button"
                                x-on:click="$dispatch(\'toggle-logout\')"
                            >
                                Cerrar sesión
                            </x-filament::button>
                        </div>');
                }
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                function (): string {
                    return Blade::render('<div x-data="{ confirmLogout: false, dark: document.documentElement.classList.contains(\'dark\') }" @toggle-logout.window="confirmLogout = true; dark = document.documentElement.classList.contains(\'dark\')" x-cloak>
                        <div
                            x-show="confirmLogout"
                            x-cloak
                            x-transition.opacity
                            style="position:fixed; top:0; right:0; bottom:0; left:0; z-index:9999; background-color:rgba(17,24,39,0.5);"
                            x-on:click.self="confirmLogout = false"
                            x-on:keydown.escape.window="confirmLogout = false"
                        >
                            <div
                                x-show="confirmLogout"
                                x-transition.opacity
                                x-bind:style="dark ? \'position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); animation:modalIn 0.35s cubic-bezier(0.16,1,0.3,1) both; width:100%; max-width:24rem; border-radius:0.75rem; background-color:#1f2937; border:1px solid #374151; padding:1.5rem; text-align:left; box-shadow:0 20px 25px -5px rgba(0,0,0,0.25); color:#f9fafb;\' : \'position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); animation:modalIn 0.35s cubic-bezier(0.16,1,0.3,1) both; width:100%; max-width:24rem; border-radius:0.75rem; background-color:#ffffff; border:1px solid #e5e7eb; padding:1.5rem; text-align:left; box-shadow:0 20px 25px -5px rgba(0,0,0,0.10),0 10px 10px -5px rgba(0,0,0,0.04); color:#111827;\'"
                                role="dialog"
                                aria-modal="true"
                            >
                                <h2 style="font-size:1rem; font-weight:700; margin:0;">Cerrar sesión</h2>
                                <p style="margin-top:0.5rem; font-size:0.875rem;">¿Estás seguro de que deseas cerrar sesión?</p>

                                <div style="margin-top:1.5rem; display:flex; justify-content:flex-end; gap:0.75rem;">
                                    <x-filament::button
                                        color="gray"
                                        outlined="true"
                                        size="sm"
                                        type="button"
                                        x-on:click="confirmLogout = false"
                                    >
                                        Cancelar
                                    </x-filament::button>

                                    <form method="POST" action="' . route('filament.admin.auth.logout') . '">
                                        ' . csrf_field() . '
                                        <x-filament::button
                                            color="danger"
                                            size="sm"
                                            type="submit"
                                        >
                                            Cerrar sesión
                                        </x-filament::button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>');
                }
            )
            ->userMenuItems([
                'logout' => MenuItem::make()->visible(false),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
