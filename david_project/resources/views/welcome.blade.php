{{--  resources/views/welcome.blade.php  --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <link rel="icon" type="image/jpeg" href="{{ asset('Capture.JPG') }}">

    <title>Moodify</title>

    {{-- ▸ Bootstrap 5  --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISPoSU20aM9J1lH/7kUYgdK3Y2YW1jzWw13HUfF0k0RPgpoXK3r5L"
          crossorigin="anonymous">
</head>

<body class="d-flex flex-column min-vh-100">

    {{-- ▸ Barre de navigation (pense à y ajouter les classes Bootstrap) --}}
    @include('partials.navbar')

    <main class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center py-5">

        <h1 class="display-5 fw-bold mb-3">
            Bienvenue sur <span class="text-primary">Moodify</span>
        </h1>

        <p class="lead text-muted mb-4">
            Découvrez votre ambiance musicale idéale grâce à nos recommandations personnalisées.
        </p>

        {{-- =========  BOUTON « Recommander ma playlist »  ========= --}}
        @auth
            @if(auth()->user()->spotifyAccount)
                {{-- ✔ connecté + Spotify lié --}}
                <a href="{{ route('playlist.recommend') }}"
                   class="btn btn-success btn-lg">
                    Recommander ma playlist
                </a>
            @else
                {{-- ✘ connecté sans Spotify --}}
                <a href="{{ route('playlist.needSpotify') }}"
                   class="btn btn-secondary btn-lg">
                    Recommander ma playlist
                </a>
            @endif
        @else
            {{-- ✘ non connecté --}}
            <a href="{{ route('login.create') }}"
               class="btn btn-primary btn-lg">
                Recommander ma playlist
            </a>
        @endauth

        {{-- Messages flash / erreurs --}}
        @if ($errors->any())
            <div class="alert alert-danger mt-4 w-100 w-md-50 mx-auto">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success mt-4 w-100 w-md-50 mx-auto">
                {{ session('status') }}
            </div>
        @endif

    </main>

    {{-- ▸ Optionnel : JS Bootstrap (modales, etc.) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-RP5SMmjoN+SNNX9TLBtzS7+wrqWq2mM/wXqw4SxnZvbXLxkjbz6Hbh2hZ3l1B9nK"
            crossorigin="anonymous"></script>
</body>
</html>
