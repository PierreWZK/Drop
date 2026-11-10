{{-- Plaque de repli (fleurs, dessinée pour le site) pour un produit sans photo ni illustration. $seed fait varier la composition. --}}
@php
    $s = (int) ($seed ?? 1);
    $marine = $s % 2 === 1;
    $fond = $marine ? '#226192' : '#EF8557';
    $rot = ($s * 37) % 45;
    $dx = (($s * 53) % 60) - 30;
    $grandeStyle = $marine ? '--p1:#EF8557;--p2:#F6B08F;--coeur:#226192;--pistil:#EAE6DE' : '--p1:#EAE6DE;--p2:#FFFFFF;--coeur:#EF8557;--pistil:#14385A';
@endphp
<svg class="plate-art" viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice" role="img" aria-label="{{ $label ?? 'Motif de fleurs' }}" width="400" height="500">
    <rect width="400" height="500" fill="{{ $fond }}"/>
    <g style="{{ $grandeStyle }}">
        <use href="#fleur" x="{{ 30 + $dx }}" y="110" width="340" height="340" transform="rotate({{ $rot }} {{ 200 + $dx }} 280)"/>
        <use href="#fleur" x="-50" y="-40" width="170" height="170" transform="rotate({{ 20 + $rot }} 35 45)"/>
        <use href="#fleur" x="290" y="400" width="150" height="150" transform="rotate({{ $rot }} 365 475)"/>
    </g>
</svg>
