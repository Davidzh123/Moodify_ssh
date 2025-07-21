<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Édition playlist – Moodify</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/jpeg" href="{{ asset('Capture.JPG') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  @include('partials.navbar')

  <div class="container py-5">
    <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Modifier la playlist</h1>

    {{-- Affiche un message de session --}}
    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Récupération du thème et de la blacklist en mémoire --}}
    @php
      $currentTheme     = session('currentTheme', 'all');
      $blacklistArtists = session('blacklistArtists', []);

      // Construction de la map artiste → thèmes
      $artistMap = [];
      foreach($rec->data as $theme => $tracks) {
        foreach($tracks as $t) {
          $id = $t['artist_id'];
          if (!isset($artistMap[$id])) {
            $artistMap[$id] = [
              'artist'       => $t['artist'],
              'artist_image' => $t['artist_image'] ?? '',
              'themes'       => [],
            ];
          }
          if (!in_array($theme, $artistMap[$id]['themes'], true)) {
            $artistMap[$id]['themes'][] = $theme;
          }
        }
      }
      $themes = array_keys($rec->data);
    @endphp

    {{-- Sélecteur de thème global --}}
    <div class="mb-4 w-auto">
      <label for="theme-select" class="form-label">Choisir un thème :</label>
      <select id="theme-select" class="form-select">
        <option value="all" {{ $currentTheme === 'all' ? 'selected' : '' }}>Tous</option>
        @foreach($themes as $t)
          <option value="{{ $t }}" {{ $currentTheme === $t ? 'selected' : '' }}>
            {{ ucfirst($t) }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Cartes ARTISTES --}}
    <h2 class="h4 mb-3">Artistes</h2>
    <div id="artists-row" class="row mb-5">
      @foreach($artistMap as $id => $artist)
        <div class="col-6 col-md-3 mb-3 artist-card"
             data-artist-id="{{ $id }}"
             data-themes="{{ implode(',', $artist['themes']) }}">
          <div class="card h-100 shadow-sm">
            @if($artist['artist_image'])
              <img src="{{ $artist['artist_image'] }}" class="card-img-top" alt="Photo {{ $artist['artist'] }}">
            @endif
            <div class="card-body text-center d-flex flex-column">
              <h5 class="card-title">{{ $artist['artist'] }}</h5>
              <button type="button" class="btn btn-outline-danger mt-auto btn-sm toggle-blacklist">
                ✖ Blacklist
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Sections Titres + nav-pills --}}
    <h2 class="h4 mb-3">Titres</h2>
    <ul id="theme-tabs" class="nav nav-pills mb-4">
      <li class="nav-item">
        <span class="nav-link active" data-theme="all">Tous</span>
      </li>
      @foreach($themes as $t)
        <li class="nav-item">
          <span class="nav-link text-capitalize" data-theme="{{ $t }}">{{ $t }}</span>
        </li>
      @endforeach
    </ul>

    @foreach($rec->data as $theme => $tracks)
      <div class="theme-section mb-4" data-theme="{{ $theme }}">
        <h3 class="h5 text-capitalize">{{ $theme }}</h3>
        <ul class="list-group">
          @foreach($tracks as $t)
            <li class="list-group-item d-flex align-items-center track-item"
                data-theme="{{ $theme }}"
                data-artist-id="{{ $t['artist_id'] }}">
              @if(!empty($t['image']))
                <img src="{{ $t['image'] }}"
                     class="me-3"
                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;"
                     alt="Cover {{ $t['name'] }}">
              @endif
              <div>{{ $t['name'] }} — <em>{{ $t['artist'] }}</em></div>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach

    {{-- Actions globales --}}
    <div class="mt-4">
      <button id="regen-btn" class="btn btn-primary">Régénérer</button>
      <button id="reset-blacklist-btn" class="btn btn-warning ms-2">Réinitialiser la blacklist</button>
      <button id="save-btn" class="btn btn-success ms-2">Enregistrer ce thème</button>
      <button class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal">Supprimer</button>
      <a href="{{ route('home') }}" class="btn btn-secondary ms-2">Retour</a>

      {{-- Formulaires cachés --}}
      <form id="regenerate-form" action="{{ route('playlist.recommend') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="theme" id="regenerate-form-theme" value="{{ $currentTheme }}">
      </form>
      <form id="save-form" action="{{ route('playlist.save') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="theme" id="save-form-theme" value="{{ $currentTheme }}">
      </form>
    </div>
  </div>

  {{-- Modals --}}
  <!-- Modal Confirmation Régénération -->
  <div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmer la régénération</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Vous allez régénérer le thème : <strong id="modal-theme-name"></strong></p>
        <p>Artistes exclus :</p>
        <ul id="confirm-artist-list" class="mb-0"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="modal-confirm" class="btn btn-primary">Valider</button>
      </div>
    </div></div>
  </div>

  <!-- Modal Reset Blacklist -->
  <div class="modal fade" id="resetBlacklistModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Réinitialiser la blacklist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Les artistes suivants seront retirés :</p>
        <ul id="reset-artist-list" class="mb-0"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="modal-reset-confirm" class="btn btn-warning">Réinitialiser</button>
      </div>
    </div></div>
  </div>

  <!-- Modal Save Thème -->
  <div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enregistrer ce thème</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Vous allez enregistrer la playlist du thème : <strong id="modal-save-theme"></strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="modal-save-confirm" class="btn btn-success">Enregistrer</button>
      </div>
    </div></div>
  </div>

  <!-- Modal Delete Playlist -->
  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Supprimer la playlist ?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><p>Cette action est définitive.</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <form action="{{ route('playlist.destroy') }}" method="POST">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const select      = document.getElementById('theme-select');
    const pills       = Array.from(document.querySelectorAll('#theme-tabs .nav-link'));
    const artists     = Array.from(document.querySelectorAll('.artist-card'));
    const tracks      = Array.from(document.querySelectorAll('.track-item'));
    const blacklisted = new Set(@json($blacklistArtists));

    function applyFilter(theme) {
      select.value = theme;
      pills.forEach(p => p.classList.toggle('active', p.dataset.theme === theme));
      artists.forEach(card => {
        const tm = card.dataset.themes.split(',');
        card.style.display = (theme==='all' || tm.includes(theme)) ? '' : 'none';
      });
      tracks.forEach(li => {
        const id  = li.dataset.artistId;
        const show = (theme==='all' || li.dataset.theme===theme)
                  && document.querySelector(`.artist-card[data-artist-id="${id}"]`).style.display !== 'none'
                  && !blacklisted.has(id);
        li.style.display = show ? '' : 'none';
      });
      document.querySelectorAll('.theme-section').forEach(sec => {
        const any = Array.from(sec.querySelectorAll('.track-item')).some(li => li.style.display !== 'none');
        sec.style.display = any ? '' : 'none';
      });
    }

    // Initial filter
    applyFilter(select.value);
    select.addEventListener('change', () => applyFilter(select.value));
    pills.forEach(p => p.addEventListener('click', () => applyFilter(p.dataset.theme)));

    // Toggle blacklist
    document.querySelectorAll('.toggle-blacklist').forEach(btn => {
      btn.addEventListener('click', () => {
        const card = btn.closest('.artist-card');
        const id   = card.dataset.artistId;
        if (blacklisted.has(id)) {
          blacklisted.delete(id);
          card.classList.remove('border-danger','opacity-50');
          btn.classList.replace('btn-success','btn-outline-danger');
          btn.innerText = '✖ Blacklist';
        } else {
          blacklisted.add(id);
          card.classList.add('border-danger','opacity-50');
          btn.classList.replace('btn-outline-danger','btn-success');
          btn.innerText = '✔ Annulé';
        }
        applyFilter(select.value);
      });
    });

    // Régénération
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    document.getElementById('regen-btn').addEventListener('click', () => {
      document.getElementById('modal-theme-name').textContent = select.value;
      const list = document.getElementById('confirm-artist-list'); list.innerHTML = '';
      blacklisted.forEach(id => {
        const name = document.querySelector(`.artist-card[data-artist-id="${id}"] .card-title`).innerText;
        const li = document.createElement('li'); li.innerText = name; list.append(li);
      });
      confirmModal.show();
    });
    document.getElementById('modal-confirm').addEventListener('click', () => {
      const form = document.getElementById('regenerate-form');
      form.querySelectorAll('input[name="blacklist_artists[]"]').forEach(i => i.remove());
      blacklisted.forEach(id => {
        const inp = document.createElement('input'); inp.type='hidden'; inp.name='blacklist_artists[]'; inp.value=id;
        form.append(inp);
      });
      document.getElementById('regenerate-form-theme').value = select.value;
      confirmModal.hide();
      form.submit();
    });

    // Reset blacklist
    const resetModal = new bootstrap.Modal(document.getElementById('resetBlacklistModal'));
    document.getElementById('reset-blacklist-btn').addEventListener('click', () => {
      const list = document.getElementById('reset-artist-list'); list.innerHTML = '';
      blacklisted.forEach(id => {
        const name = document.querySelector(`.artist-card[data-artist-id="${id}"] .card-title`).innerText;
        const li = document.createElement('li'); li.innerText = name; list.append(li);
      });
      resetModal.show();
    });
    document.getElementById('modal-reset-confirm').addEventListener('click', () => {
      blacklisted.clear();
      document.querySelectorAll('.artist-card').forEach(card => {
        card.classList.remove('border-danger','opacity-50');
        const btn = card.querySelector('.toggle-blacklist');
        btn.classList.replace('btn-success','btn-outline-danger'); btn.innerText = '✖ Blacklist';
      });
      applyFilter(select.value);
      resetModal.hide();
    });

    // Enregistrement thème
    const saveModal = new bootstrap.Modal(document.getElementById('saveModal'));
    document.getElementById('save-btn').addEventListener('click', () => {
      document.getElementById('modal-save-theme').textContent = select.value;
      saveModal.show();
    });
    document.getElementById('modal-save-confirm').addEventListener('click', () => {
      document.getElementById('save-form-theme').value = select.value;
      saveModal.hide();
      document.getElementById('save-form').submit();
    });
  </script>
</body>
</html>
