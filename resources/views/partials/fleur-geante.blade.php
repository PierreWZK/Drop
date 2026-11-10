{{-- Grande fleur granuleuse orange (forme décorative du hero et de la clôture), dessinée pour le site. --}}
@php
    mt_srand(11);
    $points = '';
    for ($i = 0; $i < 26; $i++) {
        $clair = $i % 3 === 0;
        $points .= '<circle cx="'.mt_rand(0, 44).'" cy="'.mt_rand(0, 44).'" r="'.(mt_rand(9, 24) / 10).'" fill="'.($clair ? '#F7A47E' : '#D8643A').'" opacity="'.($clair ? '.8' : '.65').'"/>';
    }
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <pattern id="grain" width="44" height="44" patternUnits="userSpaceOnUse">
            <rect width="44" height="44" fill="#EF8557"/>
            {!! $points !!}
        </pattern>
    </defs>
    <symbol id="fleur-geante" viewBox="0 0 800 800">
        <g fill="url(#grain)" stroke="#D8643A" stroke-width="3" stroke-linejoin="round">
            @for ($i = 0; $i < 8; $i++)
                <path d="M400 412 C284 372 258 150 400 14 C542 150 516 372 400 412 Z" transform="rotate({{ $i * 45 }} 400 400)"/>
            @endfor
            <g transform="rotate(22.5 400 400) translate(400 400) scale(.6) translate(-400 -400)">
                @for ($i = 0; $i < 8; $i++)
                    <path d="M400 412 C284 372 258 150 400 14 C542 150 516 372 400 412 Z" transform="rotate({{ $i * 45 }} 400 400)"/>
                @endfor
            </g>
        </g>
        <circle cx="400" cy="400" r="74" fill="#C95A2E"/>
        @for ($i = 0; $i < 12; $i++)
            <circle cx="400" cy="352" r="7" fill="#EAE6DE" transform="rotate({{ $i * 30 }} 400 400)"/>
        @endfor
        <circle cx="400" cy="400" r="14" fill="#EAE6DE"/>
    </symbol>
</svg>
