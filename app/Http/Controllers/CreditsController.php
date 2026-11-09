<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\View\View;
use League\CommonMark\Extension\Table\TableExtension;

class CreditsController extends Controller
{
    public function index(): View
    {
        $file = public_path('catalog/CREDITS.md');
        $html = is_file($file)
            ? Str::markdown(file_get_contents($file), ['html_input' => 'strip', 'allow_unsafe_links' => false], [new TableExtension])
            : '<p>Aucun crédit à afficher.</p>';

        return view('credits', ['html' => $html]);
    }
}
