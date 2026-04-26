<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Login - Cattle RFID Premium</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, #065f46 0%, #022c22 100%);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Ambient light effects */
        .auth-bg::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: var(--primary);
            filter: blur(150px);
            opacity: 0.15;
            top: -250px;
            left: -250px;
            border-radius: 50%;
        }

        .login-box {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo .icon {
            font-size: 3.5rem;
            display: block;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
        }

        .brand-logo h1 {
            color: white;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .brand-logo p {
            color: #34d399;
            font-weight: 500;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
<div class="auth-bg">
    <div class="login-box">
        <div class="brand-logo">
            <span class="icon">🐂</span>
            <h1>Cattle RFID</h1>
            <p>Gestão Pecuária Inteligente</p>
        </div>

        <x-card glass="true" style="padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <h2
                    style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 2rem; text-align: center;">
                    Acesso ao Sistema
                </h2>

                <x-input label="E-mail Administrativo" name="email" type="email" required :value="old('email')"
                         placeholder="seu@email.com" data-testid="login-email"/>

                <x-input label="Senha" name="password" type="password" required placeholder="••••••••"
                         data-testid="login-password"/>

                @if($errors->any())
                <div
                    data-testid="login-error"
                    style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--danger); padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
                            <span style="color: var(--danger); font-size: 0.875rem; font-weight: 600;">
                                {{ $errors->first() }}
                            </span>
                </div>
                @endif

                <div style="margin-top: 2rem;">
                    <x-button type="submit" fullWidth="true" data-testid="login-submit">
                        Entrar no Painel
                    </x-button>
                </div>

            </form>
        </x-card>
    </div>
</div>
</body>

</html>
