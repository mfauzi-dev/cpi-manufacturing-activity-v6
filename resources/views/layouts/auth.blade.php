<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>PT Charoen Pokphand Indonesia Tbk</title>
    <link rel="icon" href="data:,">

    <!-- General CSS Files -->
    @include('includes.style')

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
        @yield('content')
    </div>

    <!-- General JS Scripts -->
    @include('includes.script')

    @stack('scripts')
</body>

</html>
