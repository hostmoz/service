<?php


namespace SpondonIt\Service;

use Illuminate\Support\ServiceProvider;

class SpondonItServicePorvider extends ServiceProvider{

    public function boot(){
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'service');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'service');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/spondonit'),
             __DIR__.'/../config/spondonit.php' => config_path('spondonit.php'),
             __DIR__.'/../resources/views' => resource_path('views/vendor/spondonit'),
        ], 'spondonit');
       
    }

    public function register()
    {

    }
}

?>
