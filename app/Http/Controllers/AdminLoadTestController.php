<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/** Lance le test de charge depuis le serveur. Désactivé en production (APP_ENV=production). */
class AdminLoadTestController extends Controller
{
    public function form(): View
    {
        abort_if(app()->isProduction(), 404);

        return view('admin.load-test', ['output' => null]);
    }

    public function run(): View
    {
        abort_if(app()->isProduction(), 404);
        set_time_limit(600);

        $code = Artisan::call('drops:load-test', ['--requests' => 200, '--stock' => 20, '--concurrency' => 20]);

        return view('admin.load-test', ['output' => Artisan::output(), 'ok' => $code === 0]);
    }
}
