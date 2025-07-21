<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion – Moodify</title>
</head>
<body class="bg-light">

    @include('partials.navbar')

    <main class="container py-5 d-flex justify-content-center">

        {{-- ---------- Carte de connexion ---------- --}}
        <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
            <div class="card-body">

                <h1 class="h3 mb-4 text-center">Connexion</h1>

                <form action="{{ route('login.post') }}" method="POST" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse e-mail</label>
                        <input  id="email" type="email" name="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="votre@mail.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div class="mb-4">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input  id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        Se connecter
                    </button>
                </form>

            </div>
        </div>
    </main>

    {{-- JS Bootstrap (burger mobile) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
