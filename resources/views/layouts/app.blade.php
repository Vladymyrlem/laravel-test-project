<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Мій сайт')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- Google reCAPTCHA -->
    {!! NoCaptcha::renderJs() !!}

    <style>
        body {
            padding-top: 70px;
            padding-bottom: 70px;
        }
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- 🔝 Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Мій сайт</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="{{ url('/comments') }}" class="nav-link">Коментарі</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- 🧱 Main Content -->
<main class="container mt-4 mb-5">
    @yield('content')
</main>

<!-- 🔻 Footer -->
<footer class="bg-dark text-white text-center py-3">
    <div class="container">
        &copy; {{ date('Y') }} Мій сайт. Усі права захищені.
    </div>
</footer>

@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

</body>
</html>
