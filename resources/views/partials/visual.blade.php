{{-- Visuel d'un produit : photo si elle existe, sinon illustration dessinée, sinon plaque de fleurs.
     Paramètres : $name (nom du produit), $photo (URL ou null), $seed, $lazy. Le symbole #fleur doit être dans la page. --}}
@if (! empty($photo))
    <img src="{{ $photo }}" alt="{{ $name }}" width="900" height="1125" @if ($lazy ?? true) loading="lazy" @endif>
@elseif (\App\Support\ProductArt::has($name))
    {!! \App\Support\ProductArt::svg($name) !!}
@else
    @include('partials.plate', ['seed' => $seed ?? 1, 'label' => $name])
@endif
