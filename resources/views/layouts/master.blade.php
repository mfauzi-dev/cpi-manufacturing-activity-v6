<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>PT Charoen Pokphand Indonesia Tbk</title>
    <link rel="icon" href="{{ asset('stisla/dist/assets/img/logo.jfif') }}">

    <!-- General CSS Files -->
    @include('includes.style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    @stack('addon-style')

    <!-- Start GA -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-94034622-3');
    </script>
    <!-- /END GA -->
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            @include('includes.navbar')

            @if (auth()->check() && auth()->user()->role->name == 'Admin')
                @include('includes.sidebar.admin')
            @elseif(auth()->check() && auth()->user()->role->name == 'General Manager')
                @include('includes.sidebar.general-manager')
            @elseif(auth()->check() && auth()->user()->role->name == 'Manager')
                @include('includes.sidebar.manager')
            @elseif(auth()->check() && auth()->user()->role->name == 'Admin Production')
                @include('includes.sidebar.admin-production')
            @endif


            <!-- Main Content -->

            <div class="main-content">
                <section class="section">

                    @yield('content')

                </section>
            </div>
            @include('includes.footer')
        </div>
    </div>

    <!-- General JS Scripts -->
    @include('includes.script')

    @stack('scripts')
</body>

</html>
