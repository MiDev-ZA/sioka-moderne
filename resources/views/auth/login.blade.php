<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="turbo-visit-control" content="reload">
    <script>
      (function () {
        try {
          var theme = localStorage.getItem('sioka-theme');
          if (theme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
          }
        } catch (e) {}
      })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SIOKA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=DM+Serif+Display:ital@1&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --ink: #0B0B10;
            --magenta: #B31983;
            --magenta-soft: rgba(179, 25, 131, 0.16);
            --bleu: #2E9BE0;
            --blanc: #F5F5F7;
            --plomb: #9AA3B5;

            /* Le fond (photo) reste toujours sombre — seule la carte adapte son verre */
            --glass: rgba(14, 14, 20, 0.55);
            --glass-bord: rgba(255, 255, 255, 0.12);
            --blur: blur(22px);

            --glass-2: rgba(255, 255, 255, 0.06);

            /* Couleurs sensibles au thème, utilisées dans la carte de connexion */
            --card-text: #fff;

            --font-main: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-display: 'DM Serif Display', serif;
            --ease: cubic-bezier(.4, 0, .2, 1);
        }

        [data-theme="light"] {
            --glass: rgba(255, 255, 255, 0.75);
            --glass-2: rgba(11, 11, 16, 0.05);
            --glass-bord: rgba(11, 11, 16, 0.12);
            --plomb: #5b6472;
            --card-text: #14141a;
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: var(--font-main);
            background: var(--ink);
            color: var(--blanc);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            cursor: pointer;
            font-family: var(--font-main);
            border: none;
            background: none;
        }

        img {
            display: block;
            max-width: 100%;
        }

        /* ── Fond photo + voile ─────────────────────────────── */
        .bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                linear-gradient(180deg, rgba(11, 11, 16, 0.55) 0%, rgba(11, 11, 16, 0.80) 55%, rgba(11, 11, 16, 0.97) 100%),
                linear-gradient(90deg, rgba(11, 11, 16, 0.65) 0%, rgba(11, 11, 16, 0.20) 60%),
                url('https://images.unsplash.com/photo-1478737270239-2f02b77fc618?q=80&w=1740&auto=format&fit=crop') center/cover no-repeat;
        }

        .bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 78% 12%, rgba(179, 25, 131, 0.16), transparent 55%);
        }

        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ─────────────────────────────────────────── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: clamp(16px, 3vw, 32px) clamp(18px, 4vw, 48px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 9px;
            font-weight: 800;
            font-size: 19px;
            letter-spacing: -.3px;
        }

        .brand-mark {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            flex-shrink: 0;
            background: var(--magenta);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-style: italic;
            font-size: 16px;
            color: #fff;
        }

        .back {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: rgba(245, 245, 247, .65);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }

        .back:hover {
            color: #fff;
        }

        .back svg {
            width: 14px;
            height: 14px;
        }

        /* ── Corps : mobile-first (empilé), desktop (deux colonnes) ── */
        .body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 12px clamp(18px, 4vw, 48px) clamp(28px, 5vw, 56px);
            gap: clamp(28px, 5vw, 52px);
        }

        .intro {
            max-width: 460px;
            animation: fadeUp .7s var(--ease) both;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            color: var(--magenta);
            background: var(--magenta-soft);
            border: 1px solid rgba(179, 25, 131, .35);
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 18px;
        }

        .badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--magenta);
            animation: blink 1.4s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .3;
            }
        }

        .intro h1 {
            font-family: var(--font-display);
            font-style: italic;
            font-weight: 400;
            font-size: clamp(28px, 6vw, 40px);
            line-height: 1.15;
            color: #fff;
            margin-bottom: 14px;
        }

        .intro h1 b {
            font-family: var(--font-main);
            font-style: normal;
            font-weight: 800;
            color: var(--magenta);
        }

        .intro p {
            font-size: 14px;
            font-weight: 300;
            line-height: 1.7;
            color: rgba(245, 245, 247, .68);
        }

        .wave {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 24px;
            margin-top: 22px;
        }

        .wave span {
            flex: 1;
            max-width: 3px;
            border-radius: 2px;
            background: var(--magenta);
            opacity: .55;
            animation: eq 1.15s ease-in-out infinite;
        }

        .wave span:nth-child(2n) {
            animation-duration: .85s;
            opacity: .4;
        }

        .wave span:nth-child(3n) {
            animation-duration: 1.4s;
            opacity: .7;
        }

        @keyframes eq {

            0%,
            100% {
                height: 16%;
            }

            50% {
                height: 100%;
            }
        }

        @media (prefers-reduced-motion:reduce) {

            .wave span,
            .badge .dot {
                animation: none;
            }
        }

        /* ── Carte ──────────────────────────────────────────── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            width: 100%;
            max-width: 400px;
            background: var(--glass);
            backdrop-filter: var(--blur);
            -webkit-backdrop-filter: var(--blur);
            border: 1px solid var(--glass-bord);
            border-radius: 22px;
            padding: clamp(24px, 4vw, 34px);
            box-shadow: 0 30px 70px -24px rgba(0, 0, 0, .6);
            animation: fadeUp .7s .1s var(--ease) both;
        }

        .card-head {
            margin-bottom: 24px;
        }

        .card-head h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--card-text);
            margin-bottom: 5px;
        }

        .card-head p {
            font-size: 12.5px;
            color: var(--plomb);
            font-weight: 300;
        }

        .card-head p a {
            color: var(--bleu);
            font-weight: 600;
        }

        .card-head p a:hover {
            color: var(--magenta);
        }

        .field {
            margin-bottom: 14px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--plomb);
            pointer-events: none;
            transition: color .2s;
        }

        .input {
            width: 100%;
            background: var(--glass-2);
            border: 1px solid var(--glass-bord);
            border-radius: 12px;
            padding: 14px 16px 14px 42px;
            font-size: 14.5px;
            font-family: var(--font-main);
            color: var(--card-text);
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .input::placeholder {
            color: var(--plomb);
        }

        .input:focus {
            border-color: var(--magenta);
            background: rgba(255, 255, 255, .09);
        }

        .input:focus+svg,
        .input-wrap:focus-within svg {
            color: var(--magenta);
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 18px 0 22px;
        }

        .check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--plomb);
            cursor: pointer;
        }

        .check input {
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 5px;
            border: 1px solid var(--glass-bord);
            background: var(--glass-2);
            display: grid;
            place-content: center;
            cursor: pointer;
        }

        .check input::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 2px;
            background: var(--magenta);
            transform: scale(0);
            transition: transform .15s var(--ease);
        }

        .check input:checked::before {
            transform: scale(1);
        }

        .forgot {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--bleu);
        }

        .forgot:hover {
            color: #fff;
        }

        .submit {
            width: 100%;
            background: var(--magenta);
            color: #fff;
            border-radius: 12px;
            padding: 15px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            transition: transform .2s, box-shadow .2s, background .2s;
            box-shadow: 0 14px 30px -12px rgba(179, 25, 131, .55);
        }

        .submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px -12px rgba(179, 25, 131, .7);
            background: #9c1573;
        }

        .submit:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--plomb);
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--glass-bord);
        }

        .create-btn {
            display: block;
            width: 100%;
            text-align: center;
            background: var(--glass-2);
            border: 1px solid var(--glass-bord);
            color: var(--card-text);
            font-size: 13px;
            font-weight: 700;
            padding: 13px;
            border-radius: 12px;
            transition: background .2s, border-color .2s;
        }

        .create-btn:hover {
            background: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .24);
        }

        .listen {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-top: 20px;
            padding: 12px 14px;
            background: var(--glass-2);
            border: 1px solid var(--glass-bord);
            border-radius: 12px;
        }

        .listen-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            flex-shrink: 0;
            background: var(--magenta-soft);
            color: var(--magenta);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .listen-icon svg {
            width: 14px;
            height: 14px;
        }

        .listen-text {
            font-size: 11px;
            color: var(--plomb);
            line-height: 1.45;
        }

        .listen-text strong {
            color: var(--card-text);
        }

        /* ── Desktop / tablette ─────────────────────────────── */
        @media (min-width: 860px) {
            .body {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding-top: 0;
                padding-bottom: clamp(40px, 6vw, 80px);
            }

            .intro {
                padding-bottom: 6px;
            }
        }

        @media (max-width: 859px) {
            .card {
                margin: 0 auto;
            }

            .intro {
                text-align: left;
            }
        }
    </style>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icones/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/icones/favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icones/apple-touch-icon.png') }}">

    <!-- PWA -->
    <link rel="manifest" href="{{ asset('images/icones/site.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icones/web-app-manifest-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icones/web-app-manifest-512x512.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="bg"></div>

    <div class="page">
        <div class="topbar">
            <a href="#" class="logo" aria-label="SIOKA WebRadio">
                <img src="{{ asset('images/SIOKA-LogoOfficiel.png') }}" alt="SIOKA" class="logo-image">
            </a>
            <a href="{{ route('home') }}" class="back" data-turbo="false">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Retour au site
            </a>
        </div>

        <div class="body">
            <div class="intro">
                <div class="badge"><span class="dot"></span> FM 106 Mhz · En direct</div>
                <h1>Za i'Sioka <b>rivo-baovao</b> !</h1>
                <p>Connectez-vous pour accéder à votre espace SIOKA.</p>
                <div class="wave" aria-hidden="true">
                    <span style="animation-delay:0s"></span><span style="animation-delay:.1s"></span><span
                        style="animation-delay:.2s"></span>
                    <span style="animation-delay:.05s"></span><span style="animation-delay:.3s"></span><span
                        style="animation-delay:.15s"></span>
                    <span style="animation-delay:.25s"></span><span style="animation-delay:0s"></span><span
                        style="animation-delay:.2s"></span>
                    <span style="animation-delay:.1s"></span><span style="animation-delay:.3s"></span><span
                        style="animation-delay:.05s"></span>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2>Connexion</h2>
                    <p>Pas encore de compte ? <a href="#">Créer un compte</a></p>
                </div>

                <form method="POST" action="{{ route('login.post') }}" autocomplete="off" novalidate data-turbo="false" data-auth-loading="Connexion en cours…">
                    @csrf

                    @if(session('success'))
                        <div style="background:rgba(25,135,84,.15);border:1px solid rgba(25,135,84,.35);color:#b9ffd0;padding:12px;border-radius:12px;margin-bottom:16px;font-size:13px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div style="background:rgba(220,53,69,.15);border:1px solid rgba(220,53,69,.35);color:#ffb3b3;padding:12px;border-radius:12px;margin-bottom:16px;font-size:13px;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="field">
                        <div class="input-wrap">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m22 6-10 7L2 6" />
                                <path d="M2 6h20v12H2z" />
                            </svg>

                            <input
                                class="input @error('email') is-invalid @enderror"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Adresse e-mail"
                                autocomplete="email"
                                required
                                autofocus>
                        </div>

                        @error('email')
                            <small style="display:block;margin-top:6px;color:#ff8c8c;">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <div class="field">
                        <div class="input-wrap">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="4" y="11" width="16" height="9" rx="2" />
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                            </svg>

                            <input
                                class="input @error('password') is-invalid @enderror"
                                type="password"
                                name="password"
                                placeholder="Mot de passe"
                                autocomplete="current-password"
                                required>
                        </div>

                        @error('password')
                            <small style="display:block;margin-top:6px;color:#ff8c8c;">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <div class="row">
                        <label class="check">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}>
                            Se souvenir de moi
                        </label>

                        <a class="forgot" href="#">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <button type="submit" class="submit">
                        Se connecter
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('partials.loading-overlay')

</body>

</html>