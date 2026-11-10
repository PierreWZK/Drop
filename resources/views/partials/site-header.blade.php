{{-- Header des pages internes : le logo ramène à l'accueil. $statut / $statutId : pastille optionnelle. Inclut le symbole de la fleur. --}}
@include('partials.fleur-symbol')
<header class="site-header">
    <a class="logo" href="{{ route('home') }}" aria-label="Drop, retour à l'accueil">
        <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
        <span>drop</span>
    </a>
    <nav aria-label="Navigation">
        <a href="{{ route('home') }}">Accueil</a>
        <a href="{{ route('archives') }}">Archives</a>
        @isset($statut)
            <span class="statut" @isset($statutId) id="{{ $statutId }}" @endisset>{{ $statut }}</span>
        @endisset
    </nav>
</header>
