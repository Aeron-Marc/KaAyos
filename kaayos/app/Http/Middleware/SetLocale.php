<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'en';

        if ($request->session()->has('locale') && in_array($request->session()->get('locale'), ['en', 'fil'])) {
            $locale = $request->session()->get('locale');
        } elseif ($request->user()) {
            $userLang = $request->user()->language;
            if (in_array($userLang, ['Filipino', 'fil'])) {
                $locale = 'fil';
            } elseif (in_array($userLang, ['English', 'en'])) {
                $locale = 'en';
            }
        } elseif ($request->header('Accept-Language')) {
            $preferred = $request->getPreferredLanguage(['en', 'fil']);
            if ($preferred) {
                $locale = $preferred;
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
