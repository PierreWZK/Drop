<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(): View
    {
        $drops = Drop::query()
            ->with('sizes')
            ->where(fn ($q) => $q->where('closes_at', '<=', now())->orWhereNotNull('sold_out_at'))
            ->orderByDesc('opens_at')
            ->get();

        return view('archives.index', ['drops' => $drops]);
    }
}
