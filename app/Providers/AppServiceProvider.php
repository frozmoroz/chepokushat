<?php

namespace App\Providers;

use App\Services\Translator\TranslatorInterface;
use App\Services\Translator\YandexTranslator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TranslatorInterface::class, YandexTranslator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
