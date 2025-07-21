<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscription – Moodify</title>
</head>
<body class="bg-light">

    @include('partials.navbar')

    <main class="container py-5 d-flex justify-content-center">

        {{-- ---------- Carte d’inscription ---------- --}}
        <div class="card shadow-sm" style="max-width: 450px; width: 100%;">
            <div class="card-body">

                <h1 class="h3 mb-4 text-center">Inscription</h1>

                <form action="{{ route('register.store') }}" method="POST" novalidate>
                    @csrf

                    {{-- Prénom --}}
                    <div class="mb-3">
                        <label for="firstname" class="form-label">Prénom</label>
                        <input  id="firstname" type="text" name="firstname"
                                value="{{ old('firstname') }}"
                                class="form-control @error('firstname') is-invalid @enderror"
                                placeholder="Jean" required>
                        @error('firstname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nom --}}
                    <div class="mb-3">
                        <label for="lastname" class="form-label">Nom</label>
                        <input  id="lastname" type="text" name="lastname"
                                value="{{ old('lastname') }}"
                                class="form-control @error('lastname') is-invalid @enderror"
                                placeholder="Dupont" required>
                        @error('lastname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse e-mail</label>
                        <input  id="email" type="email" name="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="jean@mail.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input  id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmation --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            Confirmation du mot de passe
                        </label>
                        <input  id="password_confirmation" type="password"
                                name="password_confirmation"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                placeholder="••••••••" required>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-success w-100" type="submit">
                        S’inscrire
                    </button>
                </form>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
  