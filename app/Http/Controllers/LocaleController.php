<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (! in_array($locale, ['tr', 'en'])) {
            abort(400, 'Unsupported locale');
        }

        session(['locale' => $locale]);
        App::setLocale($locale);

        return back();
    }
}
