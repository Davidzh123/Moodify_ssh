<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Vos recommandations – Moodify</title>

  {{-- Jeton CSRF --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">
   <link rel="icon" type="image/jpeg" href="{{ asset('Capture.JPG') }}">
</head>

<body class="bg-light">
@include('partials.navbar')

<div class="container py-5">
  <h1 class="mb-4"><i class="bi bi-headphones"></i> Vos recommandations</h1>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  {{-- =====================================================================
       0. Aucune recommandation ➜ message + bouton Générer
  ======================================================================--}}
  @unless($rec)
    <p class="lead">Aucune playlist n’a encore été générée.</p>
    <form action="{{ route('playlist.recommend') }}" method="POST" class="d-inline">
      @csrf
      <button class="btn btn-primary">Générer ma première playlist</button>
    </form>
  @endunless


  {{-- =====================================================================
       1. Playlist affichée avec filtre thème
  ======================================================================--}}
  @if($rec)
    @php $themes = array_keys($rec->data); @endphp

    {{-- ---- Sélecteur de thèmes (pills) -------------------------------- --}}
    <ul id="theme-tabs" class="nav nav-pills mb-4">
      <li class="nav-item">
        <button class="nav-link active" data-theme="all">Tous</button>
      </li>
      @foreach($themes as $t)
        <li class="nav-item">
          <button class="nav-link text-capitalize" data-theme="{{ $t }}">
            {{ $t }}
          </button>
        </li>
      @endforeach
    </ul>

    {{-- ---- Titres groupés par thème ----------------------------------- --}}
    @foreach($rec->data as $theme => $tracks)
      <div class="theme-section mb-4" data-theme="{{ $theme }}">
        <h2 class="h4 text-capitalize">{{ $theme }}</h2>
        <div class="row">
          @foreach($tracks as $t)
            <div class="col-6 col-md-3 mb-3">
              <div class="card h-100 shadow-sm">
                @if(!empty($t['image'] ?? ''))
                  <img src="{{ $t['image'] }}" class="card-img-top"
                       alt="Cover {{ $t['name'] }}">
                @endif
                <div class="card-body text-center d-flex flex-column">
                  <h6 class="card-title">{{ $t['name'] }}</h6>
                  <p  class="card-text text-muted mb-0">{{ $t['artist'] }}</p>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  @endif


  {{-- =====================================================================
       2. Actions globales
  ======================================================================--}}
  <div class="mt-4">
    {{-- Régénérer (disabled quand pas de rec) --}}
    <button id="regen-btn"
            class="btn btn-primary"
            @disabled(!$rec)>
      Régénérer la playlist
    </button>

    {{-- Enregistrer --}}
    <form action="{{ route('playlist.save') }}" method="POST" class="d-inline ms-2">
      @csrf
      <button class="btn btn-success" {{ $rec ? '' : 'disabled' }}>
        Enregistrer
      </button>
    </form>

    {{-- Supprimer --}}
    <button id="deleteBtn"
            class="btn btn-outline-danger ms-2"
            data-bs-toggle="modal" data-bs-target="#deleteModal"
            {{ $rec ? '' : 'disabled' }}>
      Supprimer
    </button>

    <a href="{{ route('home') }}" class="btn btn-secondary ms-2">Retour</a>

    {{-- Formulaire réel post-régénération (invisible) --}}
    <form id="regenerate-form"
          action="{{ route('playlist.recommend') }}"
          method="POST" class="d-none">
      @csrf
    </form>
  </div>
</div>


{{-- ======================= MODALE SUPPRESSION =======================--}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
   <div class="modal-header">
     <h5 class="modal-title">Supprimer la playlist ?</h5>
     <button class="btn-close" data-bs-dismiss="modal"></button>
   </div>
   <div class="modal-body">
     <p class="mb-0">Cette action est définitive.</p>
   </div>
   <div class="modal-footer">
     <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
     <form action="{{ route('playlist.destroy') }}" method="POST">
       @csrf @method('DELETE')
       <button class="btn btn-danger">Supprimer</button>
     </form>
   </div>
  </div>
 </div>
</div>


{{-- ======================= JS =======================--}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* --------------------------------------------------
   1. Filtre de thèmes (pills)
-------------------------------------------------- */
document.querySelectorAll('#theme-tabs .nav-link').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('#theme-tabs .nav-link')
            .forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const theme = btn.dataset.theme;
    document.querySelectorAll('.theme-section').forEach(sec => {
      const show = (theme === 'all') || (sec.dataset.theme === theme);
      sec.style.display = show ? '' : 'none';
    });
  });
});

/* --------------------------------------------------
   2. Régénérer : rien à confirmer ici, on poste direct
-------------------------------------------------- */
document.getElementById('regen-btn')?.addEventListener('click', () => {
  document.getElementById('regenerate-form').submit();
});
</script>
</body>
</html>
