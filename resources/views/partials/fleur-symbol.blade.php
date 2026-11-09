{{-- Fleur à huit pétales dessinée pour le site. Couleur des pétales : `color`, cœur : `--cœur`. --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="fleur" viewBox="0 0 100 100">
        <g fill="currentColor">
            @for ($i = 0; $i < 8; $i++)
                <ellipse cx="50" cy="23" rx="11.5" ry="22" transform="rotate({{ $i * 45 }} 50 50)"/>
            @endfor
        </g>
        <circle cx="50" cy="50" r="10" style="fill: var(--coeur, #EAE6DE)"/>
    </symbol>
</svg>
