<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
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
                Dashboard
            </a>
            <a href="{{ route('admin.veterinarians.index') }}"
               class="nav-link {{ request()->routeIs('admin.veterinarians.*') ? 'active' : '' }}">
                Veterinários
            </a>
            <a href="{{ route('admin.cattle.index') }}"
               class="nav-link {{ request()->routeIs('admin.cattle.*') ? 'active' : '' }}">
                Animais
            </a>
            <a href="{{ route('admin.vaccines.index') }}"
               class="nav-link {{ request()->routeIs('admin.vaccines.*') ? 'active' : '' }}">
                Vacinas
            </a>
            <a href="{{ route('admin.workstations.index') }}"
               class="nav-link {{ request()->routeIs('admin.workstations.*') ? 'active' : '' }}">
                Estações
            </a>
            <a href="{{ route('admin.alerts') }}"
               class="nav-link {{ request()->routeIs('admin.alerts') ? 'active' : '' }}"
               style="position: relative;">
                Avisos
                @php
                    $alertCount = \Illuminate\Support\Facades\Cache::remember('alert_badge_count', 300, function () {
                        return \App\Models\Vaccine::selectRaw('rfid_tag, vaccine_type, MAX(vaccination_date) as last_vax')
                            ->groupBy('rfid_tag', 'vaccine_type')
                            ->get()
                            ->filter(fn($r) => \Carbon\Carbon::parse($r->last_vax)->addDays(150)->isPast())
                            ->count();
                    });
                @endphp
                @if($alertCount > 0)
                    <span style="margin-left: auto; background: #ef4444; color: white; font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 999px; line-height: 1.6;">
                        {{ $alertCount > 99 ? '99+' : $alertCount }}
                    </span>
                @endif
            </a>
        </nav>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.05);">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger"
                        style="width: 100%; background: none; border-color: rgba(239, 68, 68, 0.4); color: #ef4444;">
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
                        style="background: none; border: none; color: var(--danger); font-size: 1.25rem;">🚪
                </button>
            </form>
        </div>
    </header>

    <!-- Mobile Bottom Nav -->
    <nav class="bottom-nav">
        <div class="nav-list">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>🏠</span> Dashboard
            </a>
            <a href="{{ route('admin.cattle.index') }}"
               class="nav-link {{ request()->routeIs('admin.cattle.*') ? 'active' : '' }}">
                <span>🐄</span> Animais
            </a>
            <a href="{{ route('admin.vaccines.index') }}"
               class="nav-link {{ request()->routeIs('admin.vaccines.*') ? 'active' : '' }}">
                <span>💉</span> Vacinas
            </a>
            <a href="{{ route('admin.workstations.index') }}"
               class="nav-link {{ request()->routeIs('admin.workstations.*') ? 'active' : '' }}">
                <span>⚙️</span> Painel
            </a>
        </div>
    </nav>

    <main class="main-content">
        @if(session('success'))
            <x-card
                style="background-color: #dcfce7; border-color: #10b981; color: #065f46; margin-bottom: 2rem; padding: 1rem;">
                {{ session('success') }}
            </x-card>
        @endif

        @yield('content')
    </main>
</div>
    @stack('scripts')
</body>

</html>
