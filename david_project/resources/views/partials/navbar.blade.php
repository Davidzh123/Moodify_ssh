{{--  Ce <link> charge Bootstrap sur TOUTES les pages qui incluent la navbar --}}
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm mb-4">
    <div class="container-fluid">

        {{-- Logo / Accueil --}}
        <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">
            Moodify
        </a>

        {{-- Bouton « burger » mobile --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="mainNav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto gap-2">

                {{-- Lien « Accueil » accessible à tout le monde --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">Accueil</a>
                </li>

                @guest
                    {{-- Non connecté : Log in & Register --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login.create') }}">Se connecter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register.create') }}">S’inscrire</a>
                    </li>
                @else
                    {{-- Connecté : Dashboard + Déconnexion --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Dashboard</a>
                    </li>

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-link nav-link" type="submit">
                                Se déconnecter
                            </button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
