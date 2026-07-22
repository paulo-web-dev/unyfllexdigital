<?php

namespace App\Providers;

use App\Contracts\WhatsappProviderContract;
use App\Services\Whatsapp\UazapiProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Provedor de WhatsApp isolado atras do contrato: trocar a Uazapi pela
        // API oficial deve ser mudar esta linha, nao varrer o app.
        $this->app->bind(WhatsappProviderContract::class, UazapiProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
