{{-- Logo : fleur à deux couronnes de pétales dessinée pour le site.
     Couleurs : --p1 (grands pétales), --p2 (petits pétales), --coeur (cœur), --pistil (points du cœur). --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="fleur" viewBox="0 0 100 100">
        <g>
            @for ($i = 0; $i < 8; $i++)
                <path d="M50 53 C32 41 34 14 50 2 C66 14 68 41 50 53 Z" transform="rotate({{ $i * 45 }} 50 50)" style="fill: var(--p1, currentColor)"/>
            @endfor
        </g>
        <g>
            @for ($i = 0; $i < 8; $i++)
                <path d="M50 51 C39 43 40 27 50 18 C60 27 61 43 50 51 Z" transform="rotate({{ 22.5 + $i * 45 }} 50 50)" style="fill: var(--p2, #F6B08F)"/>
            @endfor
        </g>
        <g stroke-linecap="round" style="stroke: var(--p2, #F6B08F)" opacity=".7" fill="none" stroke-width="1.4">
            @for ($i = 0; $i < 8; $i++)
                <path d="M50 40 L50 12" transform="rotate({{ $i * 45 }} 50 50)"/>
            @endfor
        </g>
        <circle cx="50" cy="50" r="10.5" style="fill: var(--coeur, #14385A)"/>
        @for ($i = 0; $i < 8; $i++)
            <circle cx="50" cy="43.5" r="1.7" transform="rotate({{ $i * 45 }} 50 50)" style="fill: var(--pistil, #EAE6DE)"/>
        @endfor
        <circle cx="50" cy="50" r="2.6" style="fill: var(--pistil, #EAE6DE)"/>
    </symbol>
</svg>
