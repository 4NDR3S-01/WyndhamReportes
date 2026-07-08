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
                        overflow: visible;
                    }
                    /* Barra decorativa superior */
                    .fi-simple-main::before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 1.5rem;
                        right: 1.5rem;
                        height: 3px;
                        background: linear-gradient(90deg, #0B3B60, #0E7490, #3B4C82, #D9704A);
                        border-radius: 0 0 3px 3px;
                    }

                    /* ========== TIPOGRAFÍA ========== */
                    .fi-simple-main h2 {
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
                    .fi-simple-main label {
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

                    /* ========== INPUTS (solo texto, NO checkbox) ========== */
                    .fi-simple-main input[type="email"],
                    .fi-simple-main input[type="password"],
                    .fi-simple-main input[type="text"] {
                        color: #1e293b !important;
                        background: #ffffff !important;
                        border: 1.5px solid #e2e8f0 !important;
                        border-radius: 0.75rem !important;
                        padding: 0.625rem 0.875rem !important;
                        font-size: 0.9375rem !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease;
                    }
                    .fi-simple-main input[type="email"]:focus,
                    .fi-simple-main input[type="password"]:focus,
                    .fi-simple-main input[type="text"]:focus {
                        border-color: #0E7490 !important;
                        box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.12) !important;
                        outline: none !important;
                    }
                    .fi-simple-main input::placeholder {
                        color: #94a3b8 !important;
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
                </style>') : Blade::render('<style>
                    /* Ocultar el selector de temas duplicado */
                    .fi-theme-switcher {
                        display: none !important;
                    }
                    /* Ocultar el avatar/perfil del usuario */
                    .fi-topbar .fi-user-menu {
                        display: none !important;
                    }
                </style>'),
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                function (): string {
                    $fecha = now()->translatedFormat('l, d \d\e F');

                    return Blade::render('<div class="me-4 flex items-center gap-3" x-data="{ time: new Date().toLocaleTimeString(\'es-ES\', { hour: \'2-digit\', minute: \'2-digit\' }) }" x-init="setInterval(() => time = new Date().toLocaleTimeString(\'es-ES\', { hour: \'2-digit\', minute: \'2-digit\' }), 1000)">
                        <div class="flex items-center gap-2.5">
                            <x-heroicon-o-clock class="h-5 w-5 text-primary-500 dark:text-primary-400" />
                            <div class="leading-tight">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">' . e($fecha) . '</p>
                                <p class="text-base font-bold tracking-tight text-gray-900 dark:text-white" x-text="time"></p>
                            </div>
                        </div>

                        <button type="button" x-data="{ theme: localStorage.getItem(\'theme\') || \'light\' }" x-on:click="theme = theme === \'dark\' ? \'light\' : \'dark\'; localStorage.setItem(\'theme\', theme); document.documentElement.classList.toggle(\'dark\', theme === \'dark\'); $dispatch(\'theme-changed\', theme);" class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:ring-2 hover:ring-primary-500/50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:ring-primary-500/50 dark:hover:text-primary-400" title="Alternar Modo Oscuro/Claro">
                            <x-heroicon-o-moon x-show="theme !== \'dark\'" class="h-5 w-5" />
                            <x-heroicon-o-sun x-show="theme === \'dark\'" class="h-5 w-5" style="display: none;" x-bind:style="\'display: block;\'" />
                        </button>

                        <form method="POST" action="' . route('filament.admin.auth.logout') . '">
                            ' . csrf_field() . '
                            <x-filament::button color="danger" outlined="true" size="sm" icon="heroicon-m-arrow-right-on-rectangle" type="submit">
                                Cerrar sesión
                            </x-filament::button>
                        </form>
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
