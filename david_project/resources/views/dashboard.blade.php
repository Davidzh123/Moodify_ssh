<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Dashboard – Moodify</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">
  <link rel="icon" type="image/jpeg" href="{{ asset('Capture.JPG') }}">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  @include('partials.navbar')

  <main class="container py-5 flex-grow-1">
    <h1 class="display-5 mb-4">
      Bienvenue sur ton dashboard, {{ $user->firstname }} !
    </h1>

    @if(session('status'))
      <div class="alert alert-info">{{ session('status') }}</div>
    @elseif($errors->has('spotify'))
      <div class="alert alert-warning">{{ $errors->first('spotify') }}</div>
    @endif

    {{-- Zone Spotify --}}
    @if($user->spotifyAccount)
      <div class="alert alert-success d-flex justify-content-between align-items-center">
        <div>
          <strong>Compte Spotify associé :</strong>
          {{ $user->spotifyAccount->display_name
              ?? $user->spotifyAccount->spotify_id }}
        </div>
        <div class="btn-group">
          @if($lastRec)
            <a href="{{ route('playlist.edit') }}"
               class="btn btn-primary btn-sm">
              Modifier votre playlist
            </a>
          @else
            <form action="{{ route('playlist.recommend') }}"
                  method="POST" class="d-inline">
              @csrf
              <button class="btn btn-primary btn-sm">
                Générer ma playlist
              </button>
            </form>
          @endif
          <a href="{{ route('spotify.refresh') }}"
             class="btn btn-secondary btn-sm">
            Rafraîchir Spotify
          </a>
          <form method="POST"
                action="{{ route('spotify.unlink') }}"
                class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
              Déconnecter
            </button>
          </form>
        </div>
      </div>
    @else
      <a href="{{ route('spotify.redirect') }}"
         class="btn btn-success mb-4">
        Associer mon compte Spotify
      </a>
    @endif

    {{-- Playlist générée --}}
    @if($lastRec)
      @php $themes = array_keys($lastRec->data); @endphp
      <section class="mt-5">
        <h2 class="mb-4">Dernière playlist générée</h2>

        {{-- Sélecteur de thèmes --}}
        <ul id="theme-tabs" class="nav nav-pills mb-4">
          <li class="nav-item">
            <button class="nav-link active" data-theme="all">Tous</button>
          </li>
          @foreach($themes as $t)
            <li class="nav-item">
              <button class="nav-link text-capitalize"
                      data-theme="{{ $t }}">{{ $t }}</button>
            </li>
          @endforeach
        </ul>

        {{-- Affichage des titres par thème --}}
        @foreach($lastRec->data as $theme => $tracks)
          <div class="theme-section mb-5" data-theme="{{ $theme }}">
            <h4 class="text-capitalize">{{ $theme }}</h4>
            <ul class="list-group mb-2">
              @foreach($tracks as $t)
                <li class="list-group-item d-flex align-items-center">
                  {{-- Numéro de la piste --}}
                  <span class="me-3 text-muted">{{ $loop->iteration }}.</span>

                  @if(!empty($t['image']))
                    <img src="{{ $t['image'] }}" alt="Cover"
                         class="me-3"
                         style="width:40px;height:40px;
                                object-fit:cover;border-radius:4px;">
                  @endif

                  <div>
                    {{ $t['name'] }} — <em>{{ $t['artist'] }}</em>
                  </div>
                </li>
              @endforeach
            </ul>
            <button class="btn btn-success save-spotify-btn"
                    data-theme="{{ $theme }}">
              Enregistrer dans Spotify
            </button>
          </div>
        @endforeach
      </section>
    @endif
  </main>

  {{-- Modal « Enregistrer dans Spotify » --}}
  <div class="modal fade" id="spotifySaveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Créer la playlist sur Spotify</h5>
        <button type="button" class="btn-close"
                data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous enregistrer la playlist du thème :
           <strong id="modal-theme-name"></strong> ?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                data-bs-dismiss="modal">Annuler</button>
        <form id="spotify-save-form" method="POST"
              action="{{ route('playlist.saveSpotify') }}">
          @csrf
          <input type="hidden" name="theme" id="form-theme">
          <button type="submit" class="btn btn-success">Valider</button>
        </form>
      </div>
    </div></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Filtre par thème
    document.querySelectorAll('#theme-tabs .nav-link')
      .forEach(btn => btn.addEventListener('click', () => {
        document.querySelectorAll('#theme-tabs .nav-link')
                .forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const theme = btn.dataset.theme;
        document.querySelectorAll('.theme-section')
                .forEach(sec => {
          sec.style.display = (theme==='all'||sec.dataset.theme===theme)
                              ? '' : 'none';
        });
      }));

    // Save to Spotify
    const saveModal = new bootstrap.Modal(
      document.getElementById('spotifySaveModal')
    );
    document.querySelectorAll('.save-spotify-btn')
      .forEach(btn => btn.addEventListener('click', () => {
        const theme = btn.dataset.theme;
        document.getElementById('modal-theme-name').textContent = theme;
        document.getElementById('form-theme').value = theme;
        saveModal.show();
      }));
  </script>
</body>
</html>
