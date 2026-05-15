<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cattle RFID - Premium Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Essential Layout Logic (Keeping here for immediate render) */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background-color: var(--bg-sidebar);
            color: white;
            padding: 2rem 1.25rem;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 3rem;
            color: var(--primary-light);
            text-decoration: none;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #94a3b8;
            text-decoration: none;
            padding: 0.875rem 1rem;
            border-radius: var(--radius-md);
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .main-content {
            flex: 1;
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Mobile Adjustments */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: var(--glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            z-index: 40;
            padding: 0 1.25rem;
            align-items: center;
            justify-content: space-between;
        }

        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 72px;
            background: var(--glass);
            backdrop-filter: blur(16px);
            border-top: 1px solid var(--glass-border);
            z-index: 40;
            padding: 0 1rem;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .bottom-nav .nav-list {
            flex-direction: row;
            justify-content: space-around;
            height: 100%;
            align-items: center;
            gap: 0;
        }

        .bottom-nav .nav-link {
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.5rem;
            font-size: 0.75rem;
            background: none !important;
            box-shadow: none !important;
        }

        .bottom-nav .nav-link.active {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .mobile-header {
                display: flex;
            }

            .bottom-nav {
                display: block;
            }

            .main-content {
                padding: 1.25rem;
                padding-top: 80px;
                padding-bottom: 100px;
            }
        }

        /* ── Mobile menu overlay (slide-up sheet) ───────────────────────── */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 50;
            align-items: flex-end;
        }

        .mobile-menu-overlay.open {
            display: flex;
        }

        .mobile-menu-sheet {
            width: 100%;
            background: var(--bg-surface);
            border-radius: 1.25rem 1.25rem 0 0;
            padding: 1.25rem 1.25rem calc(1.5rem + env(safe-area-inset-bottom));
            animation: slideUp 0.22s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }

        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.875rem;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .mobile-menu-header span {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .mobile-menu-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.375rem;
            display: flex;
            align-items: center;
            border-radius: 0.5rem;
        }

        .mobile-menu-links {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .mobile-menu-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: var(--radius-md);
            transition: background 0.15s;
            background: none;
            border: none;
            width: 100%;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
        }

        .mobile-menu-link:hover,
        .mobile-menu-link.active {
            background: rgba(16, 185, 129, 0.08);
            color: var(--primary);
        }

        .mobile-menu-divider {
            height: 1px;
            background: rgba(226, 232, 240, 0.8);
            margin: 0.5rem 0;
        }

        .mobile-menu-logout {
            color: var(--danger) !important;
        }

        .mobile-menu-logout:hover {
            background: rgba(239, 68, 68, 0.08) !important;
        }

        /* More button reset */
        .nav-more-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            color: #94a3b8;
        }

        .bottom-nav .nav-link {
            position: relative;
        }

        .bottom-nav .nav-badge {
            position: absolute;
            top: 0.15rem;
            right: 0.15rem;
            margin-left: 0;
            font-size: 0.6rem;
            padding: 0.1rem 0.35rem;
        }
    </style>
</head>

<body>
<div class="app-container">
    <!-- Desktop Sidebar -->
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <span>🐂</span> Cattle RFID
        </a>

        <nav class="nav-list">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.veterinarians.index') }}"
               class="nav-link {{ request()->routeIs('admin.veterinarians.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Veterinários
            </a>
            <a href="{{ route('admin.cattle.index') }}"
               class="nav-link {{ request()->routeIs('admin.cattle.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/></svg>
                Animais
            </a>
            <a href="{{ route('admin.vaccines.index') }}"
               class="nav-link {{ request()->routeIs('admin.vaccines.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 2 4 4"/><path d="m17 7 3-3"/><path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/><path d="m9 11 4 4"/><path d="m5 19-3 3"/><path d="m14 4 6 6"/></svg>
                Vacinas
            </a>
            <a href="{{ route('admin.vaccine-types.index') }}"
               class="nav-link {{ request()->routeIs('admin.vaccine-types.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/></svg>
                Tipos de Vacina
            </a>
            <a href="{{ route('admin.workstations.index') }}"
               class="nav-link {{ request()->routeIs('admin.workstations.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                Estações
            </a>
            <a href="{{ route('admin.alerts') }}"
               class="nav-link {{ request()->routeIs('admin.alerts') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Avisos
                @if($alertCount > 0)
                <span class="nav-badge">{{ $alertCount > 99 ? '99+' : $alertCount }}</span>
                @endif
            </a>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn sidebar-logout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sair do Sistema
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Header -->
    <header class="mobile-header">
        <span style="font-weight: 800; color: var(--primary-dark);">🐂 Cattle RFID</span>
        <div style="display: flex; gap: 0.75rem;">
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit"
                        style="background: none; border: none; color: var(--danger); cursor: pointer; display: flex; align-items: center; padding: 0.25rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </header>

    <!-- Mobile Bottom Nav -->
    <nav class="bottom-nav">
        <div class="nav-list">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.cattle.index') }}"
               class="nav-link {{ request()->routeIs('admin.cattle.*') ? 'active' : '' }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/></svg>
                <span>Animais</span>
            </a>
            <a href="{{ route('admin.vaccines.index') }}"
               class="nav-link {{ request()->routeIs('admin.vaccines.*') ? 'active' : '' }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 2 4 4"/><path d="m17 7 3-3"/><path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/><path d="m9 11 4 4"/><path d="m5 19-3 3"/><path d="m14 4 6 6"/></svg>
                <span>Vacinas</span>
            </a>
            <a href="{{ route('admin.alerts') }}"
               class="nav-link {{ request()->routeIs('admin.alerts') ? 'active' : '' }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                @if(isset($alertCount) && $alertCount > 0)
                <span class="nav-badge">{{ $alertCount > 99 ? '99+' : $alertCount }}</span>
                @endif
                <span>Avisos</span>
            </a>
            <button type="button"
                    class="nav-link nav-more-btn"
                    onclick="document.getElementById('mobile-menu').classList.add('open')"
                    aria-label="Mais opções">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <span>Mais</span>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu" class="mobile-menu-overlay"
         onclick="if(event.target===this)this.classList.remove('open')">
        <div class="mobile-menu-sheet">
            <div class="mobile-menu-header">
                <span>Menu</span>
                <button type="button" class="mobile-menu-close"
                        onclick="document.getElementById('mobile-menu').classList.remove('open')"
                        aria-label="Fechar menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="mobile-menu-links">
                <a href="{{ route('admin.veterinarians.index') }}"
                   class="mobile-menu-link {{ request()->routeIs('admin.veterinarians.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Veterinários
                </a>
                <a href="{{ route('admin.vaccine-types.index') }}"
                   class="mobile-menu-link {{ request()->routeIs('admin.vaccine-types.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/></svg>
                    Tipos de Vacina
                </a>
                <a href="{{ route('admin.workstations.index') }}"
                   class="mobile-menu-link {{ request()->routeIs('admin.workstations.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    Estações
                </a>
                <div class="mobile-menu-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="mobile-menu-link mobile-menu-logout">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Sair do Sistema
                    </button>
                </form>
            </div>
        </div>
    </div>

    <main class="main-content">
        @if(session('success'))
        <div class="success-banner">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </main>
</div>
@stack('scripts')
</body>

</html>
