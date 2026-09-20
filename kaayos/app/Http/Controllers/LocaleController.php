<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    protected array $supportedLocales = ['en', 'fil'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, $this->supportedLocales)) {
            session(['locale' => $locale]);

            if ($request->user()) {
                $request->user()->update([
                    'language' => $locale === 'fil' ? 'Filipino' : 'English',
                ]);
            }
        }

        return redirect()->back();
    }
}

