<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleSwitcher extends Component
{
    public function switchLocale(string $locale): void
    {
        if (in_array($locale, ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'])) {
            Session::put('locale', $locale);
            App::setLocale($locale);

            // Set direction for RTL languages
            if (in_array($locale, ['ar', 'ur'])) {
                Session::put('direction', 'rtl');
            } else {
                Session::put('direction', 'ltr');
            }

            $this->redirect(request()->header('Referer'));
        }
    }

    public function getCurrentLocale(): string
    {
        return Session::get('locale', 'en');
    }

    public function getDirection(): string
    {
        return Session::get('direction', 'ltr');
    }

    public function render()
    {
        return view('livewire.locale-switcher');
    }
}
