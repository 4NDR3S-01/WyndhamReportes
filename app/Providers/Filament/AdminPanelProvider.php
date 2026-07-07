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
                    body {
                        /* Redujimos la capa oscura para que la imagen se vea más brillante y clara */
                        background-image: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2)), url("/images/portada.png") !important;
                        background-size: cover !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                        background-attachment: fixed !important;
                    }
                    /* Hacemos el fondo del contenedor transparente para que se vea la imagen */
                    .fi-simple-layout {
                        background-color: transparent !important;
                    }
                    /* Estilo Glassmorphism (Vidrio Esmerilado Blanco) para máxima legibilidad */
                    .fi-simple-main {
                        background-color: rgba(255, 255, 255, 0.75) !important; 
                        backdrop-filter: blur(16px) !important;
                        -webkit-backdrop-filter: blur(16px) !important;
                        border-radius: 1.5rem !important;
                        padding: 2.5rem !important;
                        box-shadow: 
                            0 20px 40px -10px rgba(0, 0, 0, 0.3),
                            inset 0 1px 0 rgba(255, 255, 255, 0.6) !important;
                        border: 1px solid rgba(255, 255, 255, 0.4) !important;
                    }
                    /* Forzar todos los textos del login en negro fijo (no se altera con dark mode) */
                    .fi-simple-main,
                    .fi-simple-main *,
                    .fi-simple-main h2,
                    .fi-simple-main label,
                    .fi-simple-main p,
                    .fi-simple-main span,
                    .fi-simple-main a:not(.fi-btn) {
                        color: #1e293b !important;
                    }
                    .fi-simple-main input,
                    .fi-simple-main .fi-input,
                    .fi-simple-main input[type="email"],
                    .fi-simple-main input[type="password"] {
                        color: #1e293b !important;
                        background-color: #ffffff !important;
                        border-color: #d1d5db !important;
                    }
                    .fi-simple-main input::placeholder {
                        color: #9ca3af !important;
                    }
                    .fi-simple-main .fi-logo {
                        margin-bottom: 1rem !important;
                    }
                    .fi-simple-main .fi-link {
                        color: #2563eb !important;
                    }
                    .fi-simple-main h2 {
                        font-weight: 700 !important;
                    }
                    .fi-simple-main label {
                        font-weight: 600 !important;
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
