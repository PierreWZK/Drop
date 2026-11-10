<?php

namespace App\Support;

/**
 * Illustrations vectorielles des produits du catalogue qui n'ont pas de photo.
 * Dessinées pour le site (aucune image générée par IA) : un vêtement ou un accessoire à plat, avec le logo fleur.
 * Le résultat n'utilise que des constantes de ce fichier : le nom du produit ne sert que de clé.
 */
class ProductArt
{
    /** nom du produit => [forme, couleur principale, options] */
    private const SPECS = [
        'Bomber Satin Aurore' => ['bomber', '#EF8557', ['trim' => '#14385a', 'logoLight' => true]],
        'Doudoune Nuage' => ['puffer', '#DDE4EB', []],
        'Parka Gabardine Sable' => ['parka', '#C6B392', []],
        'Veste en Jean Délavée' => ['denim', '#5F88AE', []],
        'Sweat Col Rond Ardoise' => ['crew', '#5E6D7C', ['logoLight' => true]],
        'Crewneck Brodé Marguerite' => ['crew', '#F0EBDD', ['daisy' => true]],
        'Zip Hoodie Cobalt' => ['hoodie', '#2A5FC4', ['logoLight' => true]],
        'Sweat Oversize Écru' => ['crew', '#E7DFCD', ['oversize' => true]],
        'T-shirt Boxy Blanc Craie' => ['tee', '#F6F3EC', ['boxy' => true]],
        'T-shirt Imprimé Soleil' => ['tee', '#EFE4CF', ['sun' => true]],
        'Débardeur Côtelé Sable' => ['tank', '#D8C3A0', []],
        'Polo Maille Piquée' => ['polo', '#1F3B60', ['logoLight' => true]],
        'T-shirt Manches Longues Rayé' => ['longsleeve', '#F4F0E6', ['stripes' => '#226192']],
        'Cargo Kaki' => ['cargo', '#8B8862', []],
        'Jean Droit Brut' => ['jeans', '#2F4B6C', []],
        'Jogging Fuselé Gris Chiné' => ['jogger', '#A9ADB2', []],
        'Pantalon Parachute Noir' => ['parachute', '#22272C', ['logoLight' => true]],
        'Sneakers Midnight Runner' => ['runner', '#14385A', ['accent' => '#EF8557', 'sole' => '#EAE6DE']],
        'Sneakers Basses Blanc Cassé' => ['sneaker', '#F2EDE1', ['sole' => '#D9B98A']],
        'Montantes Cobalt' => ['hightop', '#2A5FC4', ['sole' => '#F2EDE1']],
        'Mocassins Daim Camel' => ['loafer', '#B98440', ['sole' => '#4B3520']],
        'Sandales Cuir Naturel' => ['sandal', '#D3A66B', []],
        'Sneakers Suède Sable' => ['sneaker', '#CDB48B', ['sole' => '#EAE6DE', 'suede' => true]],
        'Casquette Brodée Fleur' => ['cap', '#226192', ['logoLight' => true]],
        'Tote Bag Toile Épaisse' => ['tote', '#E9E0C9', ['trim' => '#14385a']],
        'Sac Banane Technique' => ['fanny', '#23282D', ['accent' => '#EF8557', 'logoLight' => true]],
        'Sac à Dos Roll-Top' => ['backpack', '#D9683A', ['logoLight' => true]],
        'Chaussettes Pack Trois' => ['socks', '#14385A', []],
        'Porte-clés Cuir Brodé' => ['keychain', '#B98440', ['logoLight' => true]],
        'Montre Cadran Bleu Acier' => ['watch', '#226192', []],
    ];

    /** @return array<int, string> */
    public static function names(): array
    {
        return array_keys(self::SPECS);
    }

    public static function has(?string $name): bool
    {
        return $name !== null && isset(self::SPECS[$name]);
    }

    public static function svg(string $name): ?string
    {
        if (! self::has($name)) {
            return null;
        }

        [$shape, $base, $o] = self::SPECS[$name];
        $id = 'a'.substr(md5($name), 0, 8);
        $c = [
            'base' => $base,
            'dark' => self::mix($base, '#000000', .2),
            'deep' => self::mix($base, '#000000', .38),
            'light' => self::mix($base, '#FFFFFF', .22),
            'o' => $o,
            'id' => $id,
        ];

        $art = match ($shape) {
            'tee' => self::tee($c),
            'longsleeve' => self::longsleeve($c),
            'tank' => self::tank($c),
            'polo' => self::polo($c),
            'crew' => self::crew($c),
            'hoodie' => self::hoodie($c),
            'bomber', 'puffer', 'parka', 'denim' => self::jacket($shape, $c),
            'cargo', 'jeans', 'jogger', 'parachute' => self::pants($shape, $c),
            'sneaker', 'runner', 'hightop', 'loafer' => self::shoe($shape, $c),
            'sandal' => self::sandal($c),
            'cap' => self::cap($c),
            'tote' => self::tote($c),
            'fanny' => self::fanny($c),
            'backpack' => self::backpack($c),
            'socks' => self::socks($c),
            'keychain' => self::keychain($c),
            'watch' => self::watch($c),
        };

        // Les pièces plates et basses (chaussures, casquette...) sont agrandies pour remplir le cadre.
        $zoom = ['sneaker' => 1.12, 'runner' => 1.12, 'loafer' => 1.12, 'sandal' => 1.12, 'hightop' => 1.08, 'cap' => 1.15, 'fanny' => 1.08, 'tank' => 1.03][$shape] ?? 1.0;
        if ($zoom !== 1.0) {
            $cy = in_array($shape, ['sneaker', 'runner', 'loafer', 'sandal'], true) ? 300 : 260;
            $art = '<g transform="translate(200 250) scale('.$zoom.') translate(-200 -'.$cy.')">'.$art.'</g>';
        }

        $label = htmlspecialchars($name, ENT_QUOTES);

        return '<svg class="product-art" viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice" role="img" aria-label="Illustration : '.$label.'" width="400" height="500">'
            .'<rect width="400" height="500" fill="#E7E1D4"/>'
            .'<g style="color:#EF8557;--p1:#EF8557;--p2:#F6B08F;--coeur:#14385A;--pistil:#EAE6DE" fill="none">'
            .'<ellipse cx="200" cy="462" rx="132" ry="11" fill="#14385A" opacity=".1"/>'
            .$art
            .'</g></svg>';
    }

    // ---------- couleurs ----------

    private static function mix(string $hex, string $with, float $t): string
    {
        $a = sscanf($hex, '#%02x%02x%02x');
        $b = sscanf($with, '#%02x%02x%02x');
        $out = '';
        foreach ([0, 1, 2] as $i) {
            $out .= sprintf('%02X', (int) round($a[$i] + ($b[$i] - $a[$i]) * $t));
        }

        return '#'.$out;
    }

    // ---------- éléments communs ----------

    /** Logo fleur. $light : version claire pour les fonds foncés. */
    private static function logo(float $x, float $y, float $s, bool $light = false): string
    {
        $style = $light ? '--p1:#EAE6DE;--p2:#FFFFFF;--coeur:#EF8557;--pistil:#14385A' : '';

        return '<use href="#fleur" x="'.($x - $s / 2).'" y="'.($y - $s / 2).'" width="'.$s.'" height="'.$s.'" style="'.$style.'"/>';
    }

    private static function garment(array $c, string $d, string $extra = ''): string
    {
        return '<clipPath id="'.$c['id'].'"><path d="'.$d.'"/></clipPath>'
            .'<path d="'.$d.'" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>'
            .'<g clip-path="url(#'.$c['id'].')">'.$extra.'</g>';
    }

    private static function stripes(string $color, int $from = 70, int $to = 440, int $step = 30, int $h = 15): string
    {
        $out = '';
        for ($y = $from; $y < $to; $y += $step) {
            $out .= '<rect x="0" y="'.$y.'" width="400" height="'.$h.'" fill="'.$color.'"/>';
        }

        return $out;
    }

    private static function folds(array $c, array $paths): string
    {
        $out = '';
        foreach ($paths as $p) {
            $out .= '<path d="'.$p.'" stroke="'.$c['deep'].'" stroke-width="3" opacity=".16" stroke-linecap="round"/>';
        }

        return $out;
    }

    // ---------- hauts ----------

    private static function teePath(int $w = 0): string
    {
        $x1 = 128 - $w;
        $x2 = 272 + $w;

        return "M{$x1} 108 L172 92 Q200 124 228 92 L{$x2} 108 L".(338 + $w).' 176 L'.(302 + $w).' 222 L'.$x2.' 196 L'.$x2.' 404 L'.$x1.' 404 L'.$x1.' 196 L'.(98 - $w).' 222 L'.(62 - $w)." 176 Z";
    }

    private static function tee(array $c): string
    {
        $o = $c['o'];
        $d = self::teePath(! empty($o['boxy']) ? 14 : 0);
        $extra = self::folds($c, ['M150 250 Q166 300 150 356', 'M250 240 Q236 290 252 350']);
        $out = self::garment($c, $d, $extra);
        $out .= '<path d="M172 92 Q200 126 228 92" stroke="'.$c['deep'].'" stroke-width="9" stroke-linecap="round"/>';
        $out .= '<path d="M'.(128 - (! empty($o['boxy']) ? 14 : 0)).' 392 L'.(272 + (! empty($o['boxy']) ? 14 : 0)).' 392" stroke="'.$c['deep'].'" stroke-width="2" opacity=".35"/>';
        if (! empty($o['sun'])) {
            $out .= '<g transform="translate(200 238)"><circle r="34" fill="#EF8557"/>';
            for ($i = 0; $i < 12; $i++) {
                $out .= '<rect x="-3" y="-60" width="6" height="16" rx="3" fill="#EF8557" transform="rotate('.($i * 30).')"/>';
            }
            $out .= '<circle r="34" fill="none" stroke="#14385A" stroke-width="3"/></g>'.self::logo(200, 330, 34);
        } else {
            $out .= self::logo(200, 190, 46, ! empty($o['logoLight']));
        }

        return $out;
    }

    private static function longPath(): string
    {
        return 'M128 108 L172 92 Q200 124 228 92 L272 108 L346 188 L376 384 L334 392 L304 252 L272 222 L272 404 L128 404 L128 222 L96 252 L66 392 L24 384 L54 188 Z';
    }

    private static function longsleeve(array $c): string
    {
        $o = $c['o'];
        $extra = ! empty($o['stripes']) ? self::stripes($o['stripes'], 84, 440, 30, 15) : '';
        $extra .= self::folds($c, ['M150 260 Q166 310 150 360', 'M60 300 Q70 340 62 372']);
        $out = self::garment($c, self::longPath(), $extra);
        $out .= '<path d="M172 92 Q200 126 228 92" stroke="'.($o['stripes'] ?? $c['deep']).'" stroke-width="10" stroke-linecap="round"/>';
        $out .= '<path d="M24 384 L66 392 L68 378 L28 370 Z M376 384 L334 392 L332 378 L372 370 Z" fill="'.($o['stripes'] ?? $c['dark']).'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M128 394 L272 394" stroke="'.$c['deep'].'" stroke-width="2" opacity=".4"/>';
        // pastille claire sous le logo pour qu'il se lise sur les rayures
        $out .= '<rect x="170" y="164" width="60" height="60" rx="30" fill="'.$c['base'].'"/>'.self::logo(200, 194, 52);

        return $out;
    }

    private static function tank(array $c): string
    {
        $d = 'M152 86 Q158 150 200 156 Q242 150 248 86 L276 90 L288 168 L272 224 L272 404 L128 404 L128 224 L112 168 L124 90 Z';
        $ribs = '';
        for ($x = 130; $x < 272; $x += 14) {
            $ribs .= '<path d="M'.$x.' 160 L'.$x.' 404" stroke="'.$c['deep'].'" stroke-width="3" opacity=".18"/>';
        }
        $out = self::garment($c, $d, $ribs);
        $out .= '<path d="M152 86 Q158 150 200 156 Q242 150 248 86" stroke="'.$c['deep'].'" stroke-width="7" stroke-linecap="round"/>';
        $out .= self::logo(200, 214, 42);

        return $out;
    }

    private static function polo(array $c): string
    {
        $out = self::garment($c, self::teePath(), self::folds($c, ['M150 260 Q166 310 150 360']));
        $out .= '<path d="M172 92 L200 142 L228 92 Q200 112 172 92 Z" fill="'.$c['deep'].'"/>';
        $out .= '<path d="M172 92 L198 128 L176 156 L146 114 Z M228 92 L202 128 L224 156 L254 114 Z" fill="'.$c['light'].'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>';
        $out .= '<path d="M200 142 L200 232" stroke="'.$c['deep'].'" stroke-width="3"/>';
        foreach ([160, 188, 216] as $y) {
            $out .= '<circle cx="200" cy="'.$y.'" r="4" fill="#EAE6DE" stroke="'.$c['deep'].'" stroke-width="1.5"/>';
        }
        $out .= self::logo(238, 190, 28, true);

        return $out;
    }

    private static function crew(array $c): string
    {
        $o = $c['o'];
        $over = ! empty($o['oversize']);
        $d = 'M120 110 L170 94 Q200 118 230 94 L280 110 L352 196 L372 400 L322 408 L296 262 L280 236 L280 408 L120 408 L120 236 L104 262 L78 408 L28 400 L48 196 Z';
        $out = '<g'.($over ? ' transform="translate(200 260) scale(1.12 1) translate(-200 -260)"' : '').'>';
        $out .= self::garment($c, $d, self::folds($c, ['M146 270 Q164 320 146 372', 'M62 300 Q74 346 66 380']));
        $out .= '<path d="M170 94 Q200 130 230 94" stroke="'.$c['deep'].'" stroke-width="11" stroke-linecap="round"/>';
        $out .= '<path d="M120 392 L280 392 L280 408 L120 408 Z M28 384 L78 392 L78 408 L28 400 Z M372 384 L322 392 L322 408 L372 400 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '</g>';
        $out .= ! empty($o['daisy'])
            ? '<g style="--p1:#FFFFFF;--p2:#F3EEE2;--coeur:#EF8557;--pistil:#FFFFFF">'.self::logo(176, 160, 54).'</g>'
            : self::logo(200, 200, 50, ! empty($o['logoLight']));

        return $out;
    }

    private static function hoodie(array $c): string
    {
        $d = 'M120 120 L170 104 Q200 128 230 104 L280 120 L352 204 L372 404 L322 412 L296 270 L280 244 L280 412 L120 412 L120 244 L104 270 L78 412 L28 404 L48 204 Z';
        $out = '<path d="M148 118 Q142 40 200 34 Q258 40 252 118 Q200 142 148 118 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= self::garment($c, $d, self::folds($c, ['M146 300 Q164 340 146 392']));
        $out .= '<path d="M160 112 Q200 150 240 112 Q236 70 200 66 Q164 70 160 112 Z" fill="'.$c['deep'].'"/>';
        $out .= '<path d="M200 136 L200 412" stroke="#D5D9DD" stroke-width="5"/><rect x="195" y="140" width="10" height="18" rx="2" fill="#D5D9DD"/>';
        $out .= '<path d="M184 132 L180 196 M216 132 L220 196" stroke="#EAE6DE" stroke-width="3" stroke-linecap="round"/>';
        $out .= '<path d="M142 318 L258 318 L274 392 L126 392 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2" opacity=".85"/>';
        $out .= '<path d="M120 396 L280 396 L280 412 L120 412 Z M28 388 L78 396 L78 412 L28 404 Z M372 388 L322 396 L322 412 L372 404 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= self::logo(240, 220, 36, true);

        return $out;
    }

    // ---------- vestes ----------

    private static function jacket(string $type, array $c): string
    {
        $o = $c['o'];
        $trim = $o['trim'] ?? $c['deep'];
        $long = $type === 'parka';
        $hem = $long ? 432 : ($type === 'puffer' ? 404 : 362);
        $d = 'M124 112 L172 96 Q200 120 228 96 L276 112 L352 196 L370 '.($hem + 36).' L324 '.($hem + 44).' L298 268 L280 246 L280 '.$hem.' L120 '.$hem.' L120 246 L102 268 L76 '.($hem + 44).' L30 '.($hem + 36).' L48 196 Z';
        $out = '';
        if ($type === 'parka') {
            $out .= '<path d="M148 118 Q142 40 200 34 Q258 40 252 118 Q200 142 148 118 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        }
        $extra = self::folds($c, ['M146 280 Q160 330 146 380']);
        if ($type === 'puffer') {
            foreach ([170, 214, 258, 302, 346, 390] as $y) {
                $extra .= '<path d="M20 '.$y.' Q200 '.($y + 18).' 380 '.$y.'" stroke="'.$c['deep'].'" stroke-width="3" opacity=".45"/>';
            }
        }
        if ($type === 'denim') {
            $extra .= '<ellipse cx="170" cy="260" rx="60" ry="40" fill="#FFFFFF" opacity=".12"/><ellipse cx="240" cy="320" rx="40" ry="28" fill="#FFFFFF" opacity=".1"/>';
        }
        $out .= self::garment($c, $d, $extra);

        if ($type === 'bomber') {
            $out .= '<path d="M172 96 Q200 130 228 96" stroke="'.$trim.'" stroke-width="13" stroke-linecap="round"/>';
            $out .= '<path d="M120 346 L280 346 L280 362 L120 362 Z" fill="'.$trim.'"/>';
            $out .= '<path d="M32 388 L76 398 L74 410 L30 400 Z M368 388 L324 398 L326 410 L370 400 Z" fill="'.$trim.'"/>';
            $out .= '<path d="M200 122 L200 362" stroke="#EAE6DE" stroke-width="4"/>';
            $out .= '<path d="M146 296 L146 334 M254 296 L254 334" stroke="'.$c['deep'].'" stroke-width="4" stroke-linecap="round"/>';
            $out .= self::logo(240, 190, 32, true);
        } elseif ($type === 'puffer') {
            $out .= '<path d="M158 100 Q200 70 242 100 L242 124 Q200 150 158 124 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
            $out .= '<path d="M200 138 L200 404" stroke="#9AA6B1" stroke-width="4"/>';
            $out .= self::logo(240, 200, 30);
        } elseif ($type === 'parka') {
            $out .= '<path d="M160 112 Q200 150 240 112 Q236 70 200 66 Q164 70 160 112 Z" fill="'.$c['deep'].'"/>';
            $out .= '<path d="M200 136 L200 432" stroke="#EAE6DE" stroke-width="4"/>';
            $out .= '<path d="M120 292 Q200 306 280 292" stroke="'.$c['deep'].'" stroke-width="4" opacity=".7"/>';
            $out .= '<path d="M138 340 L184 340 L184 372 L138 372 Z M216 340 L262 340 L262 372 L216 372 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
            $out .= '<path d="M184 132 L180 196 M216 132 L220 196" stroke="#EAE6DE" stroke-width="3" stroke-linecap="round"/>';
            $out .= self::logo(242, 200, 30, true);
        } else { // denim
            $out .= '<path d="M172 96 L198 126 L176 158 L142 112 Z M228 96 L202 126 L224 158 L258 112 Z" fill="'.$c['light'].'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>';
            $out .= '<path d="M200 130 L200 362" stroke="'.$c['deep'].'" stroke-width="3"/>';
            foreach ([160, 214, 268, 322] as $y) {
                $out .= '<circle cx="200" cy="'.$y.'" r="5" fill="#EAE6DE" stroke="'.$c['deep'].'" stroke-width="1.5"/>';
            }
            $out .= '<path d="M138 178 L184 178 L184 214 L138 214 Z M216 178 L262 178 L262 214 L216 214 Z" fill="none" stroke="'.$c['deep'].'" stroke-width="2" stroke-dasharray="5 4"/>';
            $out .= '<path d="M120 350 L280 350" stroke="'.$c['deep'].'" stroke-width="2" stroke-dasharray="5 4"/>';
            $out .= self::logo(161, 196, 22, true);
        }

        return $out;
    }

    // ---------- bas ----------

    private static function pants(string $type, array $c): string
    {
        $d = match ($type) {
            'jogger' => 'M132 76 L268 76 L284 300 L254 432 L212 432 L200 200 L188 432 L146 432 L116 300 Z',
            'parachute' => 'M120 76 L280 76 L336 432 L218 432 L200 190 L182 432 L64 432 Z',
            default => 'M130 76 L270 76 L288 432 L214 432 L200 196 L186 432 L112 432 Z',
        };
        $extra = self::folds($c, ['M150 220 Q158 300 150 380', 'M250 220 Q242 300 250 380']);
        $out = self::garment($c, $d, $extra);
        $out .= '<path d="M128 70 L272 70 L272 102 L128 102 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        foreach ([148, 180, 220, 252] as $x) {
            $out .= '<rect x="'.($x - 3).'" y="66" width="6" height="22" rx="2" fill="'.$c['deep'].'" opacity=".6"/>';
        }
        $out .= '<path d="M200 102 L200 196" stroke="'.$c['deep'].'" stroke-width="3"/>';
        $out .= '<path d="M134 106 Q164 140 170 190 M266 106 Q236 140 230 190" stroke="'.$c['deep'].'" stroke-width="3" opacity=".7"/>';

        if ($type === 'cargo') {
            $out .= '<path d="M116 270 L158 270 L158 330 L112 330 Z M242 270 L284 270 L288 330 L242 330 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
            $out .= '<path d="M116 286 L158 286 M242 286 L284 286" stroke="'.$c['deep'].'" stroke-width="2"/>';
            $out .= '<circle cx="137" cy="286" r="3" fill="'.$c['deep'].'"/><circle cx="263" cy="286" r="3" fill="'.$c['deep'].'"/>';
            $out .= self::logo(263, 312, 24, true);
        } elseif ($type === 'jeans') {
            $out .= '<rect x="232" y="124" width="30" height="30" rx="4" fill="#B98440" stroke="'.$c['deep'].'" stroke-width="1.5"/>'.self::logo(247, 139, 22, true);
            $out .= '<path d="M136 126 L170 126 M230 126 L264 126" stroke="#F2C27A" stroke-width="3" stroke-dasharray="5 4"/>';
            $out .= '<circle cx="136" cy="108" r="4" fill="#C98F55"/><circle cx="264" cy="108" r="4" fill="#C98F55"/>';
            $out .= '<path d="M186 432 L186 200 M214 432 L214 200" stroke="#F2C27A" stroke-width="2" stroke-dasharray="5 4" opacity=".7"/>';
        } elseif ($type === 'jogger') {
            $out .= '<path d="M188 102 L184 150 M212 102 L216 150" stroke="#EAE6DE" stroke-width="3" stroke-linecap="round"/>';
            $out .= '<path d="M142 406 L258 406 L254 432 L146 432 Z" fill="'.$c['dark'].'" opacity=".7"/>';
            $out .= self::logo(236, 150, 30);
        } else { // parachute
            $out .= '<path d="M96 380 Q200 396 304 380" stroke="'.$c['deep'].'" stroke-width="4" opacity=".7"/>';
            $out .= self::logo(244, 150, 26, true);
        }

        return $out;
    }

    // ---------- chaussures ----------

    private static function shoe(string $type, array $c): string
    {
        $o = $c['o'];
        $sole = $o['sole'] ?? '#EAE6DE';
        $acc = $o['accent'] ?? $c['dark'];
        $out = '';

        if ($type === 'loafer') {
            $out .= '<path d="M58 336 Q54 290 100 278 L200 270 Q262 262 332 302 Q362 318 354 338 L62 338 Z" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>';
            $out .= '<path d="M58 336 L354 338 L352 352 Q340 360 300 360 L90 360 Q60 358 58 336 Z" fill="'.$sole.'"/>';
            $out .= '<path d="M60 340 L100 340 L96 362 L62 358 Z" fill="'.$c['deep'].'"/>';
            $out .= '<path d="M176 276 L228 272 L234 304 L182 308 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
            $out .= '<rect x="194" y="286" width="26" height="8" rx="4" fill="'.$c['deep'].'"/>';
            $out .= self::logo(292, 316, 30, true);
            $out .= '<path d="M104 280 Q150 262 196 272" stroke="'.$c['deep'].'" stroke-width="3" opacity=".6"/>';
            for ($i = 0; $i < 30; $i++) { // grain du daim
                $out .= '<circle cx="'.(90 + ($i * 53) % 250).'" cy="'.(284 + ($i * 29) % 46).'" r="1.6" fill="'.$c['light'].'" opacity=".5"/>';
            }

            return $out;
        }

        $high = $type === 'hightop';
        $thick = $type === 'runner' ? 8 : 0;
        $upper = $high
            ? 'M62 318 Q56 150 116 118 L170 128 Q178 186 198 228 Q234 258 272 268 Q350 282 356 318 Z'
            : 'M62 318 Q58 262 96 248 L150 226 Q176 216 196 228 Q232 258 270 268 Q348 282 356 318 Z';

        $out .= '<path d="'.$upper.'" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>';
        $out .= '<path d="M290 276 Q346 286 356 318 L288 318 Q294 296 290 276 Z" fill="'.$c['dark'].'"/>';
        $out .= '<path d="M62 318 Q58 262 96 248 L112 292 L106 318 Z" fill="'.$c['dark'].'"/>';
        if ($high) {
            $out .= '<path d="M62 318 Q56 150 116 118 L112 292 Z" fill="'.$c['dark'].'"/>';
            $out .= '<path d="M64 168 Q92 150 118 150" stroke="#EAE6DE" stroke-width="7"/>';
            foreach ([0, 1, 2, 3] as $i) {
                $out .= '<path d="M'.(128 + $i * 14).' '.(150 + $i * 18).' L'.(166 + $i * 12).' '.(168 + $i * 18).'" stroke="#EAE6DE" stroke-width="4" stroke-linecap="round"/>';
            }
            $out .= '<circle cx="92" cy="226" r="22" fill="#EAE6DE" stroke="'.$c['deep'].'" stroke-width="2"/>'.self::logo(92, 226, 28);
        } else {
            foreach ([0, 1, 2] as $i) {
                $out .= '<path d="M'.(150 + $i * 18).' '.(232 - $i * 4).' L'.(178 + $i * 18).' '.(262 - $i * 2).'" stroke="#EAE6DE" stroke-width="4" stroke-linecap="round"/>';
            }
            $out .= '<path d="M96 248 L150 226" stroke="'.$c['deep'].'" stroke-width="3"/>';
            if ($type === 'runner') {
                $out .= '<path d="M110 306 Q190 280 296 296" stroke="'.$acc.'" stroke-width="14" stroke-linecap="round"/>';
                $out .= '<path d="M130 262 Q170 246 210 262" stroke="'.$c['deep'].'" stroke-width="2" stroke-dasharray="4 4"/>';
                $out .= self::logo(318, 304, 26, true);
            } else {
                $out .= self::logo(206, 284, 34, ! empty($o['suede']) ? false : in_array($c['base'], ['#14385A'], true));
            }
            if (! empty($o['suede'])) {
                for ($i = 0; $i < 36; $i++) {
                    $out .= '<circle cx="'.(80 + ($i * 47) % 260).'" cy="'.(236 + ($i * 31) % 78).'" r="1.6" fill="'.$c['light'].'" opacity=".55"/>';
                }
            }
        }
        $out .= '<path d="M'.(52 - $thick / 2).' 322 L358 322 L362 '.(336 + $thick).' Q362 '.(356 + $thick).' 336 '.(356 + $thick).' L74 '.(356 + $thick).' Q50 '.(356 + $thick).' 50 '.(336 + $thick).' Z" fill="'.$sole.'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>';
        $out .= '<path d="M58 '.(342 + $thick).' L354 '.(342 + $thick).'" stroke="'.$c['deep'].'" stroke-width="2" opacity=".25"/>';

        return $out;
    }

    private static function sandal(array $c): string
    {
        $out = '<path d="M60 336 Q56 304 120 300 L300 304 Q352 310 354 336 Q352 356 300 358 L100 358 Q62 358 60 336 Z" fill="'.$c['light'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M60 344 Q62 366 100 368 L300 368 Q348 366 354 344 L354 336 Q352 356 300 358 L100 358 Q62 358 60 336 Z" fill="#7A5A33"/>';
        $out .= '<path d="M140 304 Q200 232 262 306" stroke="'.$c['base'].'" stroke-width="26" stroke-linecap="round"/>';
        $out .= '<path d="M140 304 Q200 232 262 306" stroke="'.$c['deep'].'" stroke-width="2" opacity=".5"/>';
        $out .= '<path d="M270 308 Q306 268 338 312" stroke="'.$c['base'].'" stroke-width="20" stroke-linecap="round"/>';
        $out .= '<circle cx="198" cy="262" r="9" fill="#C9CED3" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= self::logo(112, 330, 30);

        return $out;
    }

    // ---------- accessoires ----------

    private static function cap(array $c): string
    {
        $out = '<path d="M70 284 Q200 262 330 284 Q374 296 322 322 Q200 342 78 316 Q36 298 70 284 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M96 288 Q96 150 200 140 Q304 150 304 288 Q200 306 96 288 Z" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M200 142 L200 296 M152 152 Q142 222 148 294 M248 152 Q258 222 252 294" stroke="'.$c['deep'].'" stroke-width="2.5" opacity=".4"/>';
        $out .= '<circle cx="200" cy="142" r="7" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M96 288 Q200 306 304 288" stroke="'.$c['deep'].'" stroke-width="5" opacity=".5"/>';
        $out .= self::logo(200, 214, 68, true);

        return $out;
    }

    private static function tote(array $c): string
    {
        $trim = $c['o']['trim'] ?? $c['deep'];
        $out = '<path d="M146 200 C142 86 258 86 254 200" stroke="'.$trim.'" stroke-width="14" stroke-linecap="round" opacity=".55"/>';
        $out .= '<path d="M104 196 L296 196 L310 430 L90 430 Z" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2" stroke-linejoin="round"/>';
        $out .= '<path d="M158 200 C156 108 244 108 242 200" stroke="'.$trim.'" stroke-width="14" stroke-linecap="round"/>';
        $out .= '<path d="M108 216 L292 216" stroke="'.$c['deep'].'" stroke-width="2" stroke-dasharray="6 5" opacity=".6"/>';
        $out .= '<path d="M96 414 L304 414" stroke="'.$c['deep'].'" stroke-width="2" stroke-dasharray="6 5" opacity=".5"/>';
        $out .= self::folds($c, ['M130 250 Q140 330 128 400', 'M270 250 Q262 330 274 400']);
        $out .= self::logo(200, 316, 116);

        return $out;
    }

    private static function fanny(array $c): string
    {
        $acc = $c['o']['accent'] ?? '#EF8557';
        $out = '<path d="M72 268 L16 262 L16 288 L72 292 M328 268 L384 262 L384 288 L328 292" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<rect x="338" y="258" width="34" height="36" rx="5" fill="#C9CED3" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M70 252 Q70 200 130 196 L270 196 Q330 200 330 252 L330 300 Q330 332 290 332 L110 332 Q70 332 70 300 Z" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M90 238 Q200 222 310 238" stroke="'.$acc.'" stroke-width="7" stroke-linecap="round" stroke-dasharray="2 9"/>';
        $out .= '<path d="M96 272 Q200 258 304 272 L304 312 Q200 324 96 312 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<rect x="196" y="256" width="9" height="20" rx="3" fill="'.$acc.'"/>';
        $out .= self::logo(200, 298, 38, true);

        return $out;
    }

    private static function backpack(array $c): string
    {
        $out = '<path d="M112 176 Q80 270 98 392 M288 176 Q320 270 302 392" stroke="'.$c['deep'].'" stroke-width="16" stroke-linecap="round" opacity=".8"/>';
        $out .= '<path d="M118 150 Q118 112 160 108 L240 108 Q282 112 282 150 L288 420 Q288 440 268 440 L132 440 Q112 440 112 420 Z" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M122 108 Q122 72 160 68 L240 68 Q278 72 278 108 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M126 88 L274 88" stroke="'.$c['deep'].'" stroke-width="2" opacity=".5"/>';
        $out .= '<path d="M164 108 L164 158 M236 108 L236 158" stroke="'.$c['deep'].'" stroke-width="12" stroke-linecap="round"/>';
        $out .= '<rect x="156" y="152" width="16" height="14" rx="3" fill="#C9CED3" stroke="'.$c['deep'].'" stroke-width="2"/><rect x="228" y="152" width="16" height="14" rx="3" fill="#C9CED3" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M134 296 L266 296 L272 414 L128 414 Z" fill="'.$c['dark'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M138 312 Q200 322 262 312" stroke="#EAE6DE" stroke-width="4" stroke-dasharray="2 8" stroke-linecap="round"/>';
        $out .= self::logo(200, 362, 56, true);

        return $out;
    }

    private static function socks(array $c): string
    {
        $sock = fn (string $fill, string $band, string $tx, string $rot) => '<g transform="translate('.$tx.') rotate('.$rot.')">'
            .'<path d="M0 0 L58 0 L58 120 Q58 156 100 168 L142 184 Q172 198 162 224 Q150 246 118 240 L40 220 Q0 206 0 164 Z" fill="'.$fill.'" stroke="#14385A" stroke-width="2"/>'
            .'<path d="M0 0 L58 0 L58 22 L0 22 Z M0 34 L58 34 L58 42 L0 42 Z" fill="'.$band.'"/>'
            .'<path d="M96 196 Q132 204 150 222" stroke="'.$band.'" stroke-width="9" stroke-linecap="round"/>'
            .'</g>';

        return $sock('#EAE6DE', '#EF8557', '70 96', '-10')
            .$sock('#EF8557', '#14385A', '160 120', '4')
            .$sock('#14385A', '#EAE6DE', '244 90', '12')
            .self::logo(200, 400, 34);
    }

    private static function keychain(array $c): string
    {
        $out = '<circle cx="204" cy="152" r="38" stroke="#AEB4BA" stroke-width="9"/>';
        $out .= '<g transform="rotate(-8 215 280)"><path d="M150 232 Q150 202 180 202 L250 202 Q280 202 280 232 L280 336 Q280 366 250 366 L180 366 Q150 366 150 336 Z" fill="'.$c['base'].'" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<circle cx="215" cy="226" r="10" fill="#E7E1D4" stroke="'.$c['deep'].'" stroke-width="2"/>';
        $out .= '<path d="M160 240 Q160 214 184 212 L246 212 Q270 214 270 240 L270 332 Q270 356 246 356 L184 356 Q160 356 160 332 Z" stroke="#EAE6DE" stroke-width="2.5" stroke-dasharray="6 5" opacity=".85"/>';
        $out .= self::logo(215, 296, 86, true).'</g>';

        return $out;
    }

    private static function watch(array $c): string
    {
        $strap = '#7A5230';
        $out = '<path d="M168 30 L232 30 L240 170 L160 170 Z M160 330 L240 330 L232 470 L168 470 Z" fill="'.$strap.'" stroke="#4B3018" stroke-width="2"/>';
        $out .= '<path d="M200 330 L200 370 M184 400 L216 400" stroke="#4B3018" stroke-width="3" stroke-dasharray="3 7" stroke-linecap="round"/>';
        $out .= '<rect x="296" y="238" width="20" height="24" rx="4" fill="#AEB4BA" stroke="#14385A" stroke-width="2"/>';
        $out .= '<circle cx="200" cy="250" r="104" fill="#C9CED3" stroke="#14385A" stroke-width="3"/>';
        $out .= '<circle cx="200" cy="250" r="88" fill="'.$c['base'].'" stroke="#14385A" stroke-width="3"/>';
        for ($i = 0; $i < 12; $i++) {
            $out .= '<rect x="197" y="168" width="6" height="'.($i % 3 === 0 ? 20 : 11).'" rx="2" fill="#EAE6DE" transform="rotate('.($i * 30).' 200 250)"/>';
        }
        $out .= '<path d="M200 250 L200 192" stroke="#EAE6DE" stroke-width="8" stroke-linecap="round"/><path d="M200 250 L252 268" stroke="#EAE6DE" stroke-width="5" stroke-linecap="round"/><circle cx="200" cy="250" r="7" fill="#EF8557"/>';
        $out .= self::logo(200, 296, 30, true);

        return $out;
    }
}
