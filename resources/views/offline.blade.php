<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Harviana - Offline</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @include('partials.pwa')
    <style>
        :root {
            color-scheme: light;
            font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f7f8f0;
            color: #17210d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: var(--harviana-viewport-height, 100dvh);
            margin: 0;
            display: grid;
            place-items: center;
            padding: calc(24px + env(safe-area-inset-top, 0px)) calc(24px + env(safe-area-inset-right, 0px)) calc(24px + env(safe-area-inset-bottom, 0px)) calc(24px + env(safe-area-inset-left, 0px));
            background:
                linear-gradient(135deg, rgba(217, 228, 194, 0.95), rgba(247, 248, 240, 0.98)),
                #f7f8f0;
        }

        main {
            width: min(100%, 520px);
            padding: 32px;
            border: 1px solid rgba(77, 124, 15, 0.16);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 60px rgba(23, 33, 13, 0.14);
            text-align: center;
        }

        img {
            width: 160px;
            height: auto;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0;
            font-size: clamp(2rem, 8vw, 3rem);
            line-height: 1;
        }

        p {
            margin: 16px 0 0;
            color: #47523c;
            line-height: 1.6;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 28px;
        }

        a {
            min-width: 132px;
            border-radius: 999px;
            padding: 12px 18px;
            color: #17210d;
            font-weight: 700;
            text-decoration: none;
        }

        .primary {
            background: #a3e635;
        }

        .secondary {
            border: 1px solid rgba(77, 124, 15, 0.22);
            background: #ffffff;
        }
    </style>
</head>
<body>
    <main>
        <img src="{{ asset('images/HarvianaLogo.png') }}" alt="Harviana">
        <h1>You are offline</h1>
        <p>
            Harviana saved what it could from your recent visit. Reconnect to refresh live weather, maps, predictions, forum updates, and account changes.
        </p>
        <nav aria-label="Offline navigation">
            <a class="primary" href="/dashboard">Dashboard</a>
            <a class="secondary" href="/">Home</a>
        </nav>
    </main>
</body>
</html>
