<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Agex Change</title>

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/faveicone.png') }}">

        <!-- Icons css  (Mandatory in All Pages) -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">

        <!-- App css  (Mandatory in All Pages) -->
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css">

        <!-- Tailwind est désormais compilé par Vite (voir vite.config.js + resources/css/app.css).
             Le script CDN runtime a été retiré : il était redondant et moins fiable
             (aucun plugin de formulaires, pas de cache, dépendance à un CDN externe). -->

        <link
            rel="stylesheet"
            data-purpose="Layout StyleSheet"
            title="Web Awesome"
            href="/css/app-wa-462758aa1e172f82d39e1ea35e919e0a.css?vsn=d"
        >

        <!-- Icônes : le projet utilise Google Material Symbols (self-hosted via icons.min.css,
             déjà chargé ci-dessus) sur toute l'application. Les liens FontAwesome CDN ci-dessous
             ont été retirés : ils renvoyaient une erreur 403 Forbidden (licence/kit invalide),
             et FontAwesome n'était de toute façon jamais bundlé localement dans le projet. -->

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>

        <!-- Plugin Js (Mandatory in All Pages) -->
        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/preline/preline.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/iconify-icon/iconify-icon.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

        <!-- App Js (Mandatory in All Pages) -->
        <script src="{{ asset('assets/js/app.js') }}"></script>

        <!-- Apexcharts js -->
        <script defer src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

        <!-- Morris Js Chart -->
        <script src="{{ asset('assets/libs/morris.js/morris.min.js') }}"></script>

        <script src="{{ asset('assets/libs/raphael/raphael.min.js') }}"></script>

        <!-- Dashboard Project Page js -->
        {{-- <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script> --}}

    </body>
</html>
