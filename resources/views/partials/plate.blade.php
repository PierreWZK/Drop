{{-- Plaque décorative (motif de fleurs, dessinée pour le site) pour un produit sans photo. $seed fait varier la composition. --}}
@php
    $s = (int) ($seed ?? 1);
    $bleu = $s % 2 === 1;
    $fond = $bleu ? 'var(--ink)' : 'var(--accent)';
    $grande = $bleu ? 'var(--accent)' : 'var(--ground)';
    $petite = $bleu ? 'var(--ground)' : 'var(--ink)';
    $rot = ($s * 37) % 45;
    $dx = (($s * 53) % 60) - 30;
@endphp
<svg class="plate-art" viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice" role="img" aria-label="{{ $label ?? 'Motif de fleurs' }}" width="400" height="500">
    <rect width="400" height="500" style="fill: {{ $fond }}"/>
    <g style="color: {{ $grande }}; --coeur: {{ $fond }}">
        <use href="#fleur" x="{{ 30 + $dx }}" y="110" width="340" height="340" transform="rotate({{ $rot }} {{ 200 + $dx }} 280)"/>
    </g>
    <g style="color: {{ $petite }}; --coeur: {{ $fond }}">
        <use href="#fleur" x="-50" y="-40" width="170" height="170" transform="rotate({{ 20 + $rot }} 35 45)"/>
        <use href="#fleur" x="290" y="400" width="150" height="150" transform="rotate({{ $rot }} 365 475)"/>
    </g>
</svg>
