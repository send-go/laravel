<?php

namespace Sendgo\Laravel;

use Illuminate\Support\ServiceProvider;
use Sendgo\Php\Sendgo;

class SendgoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sendgo.php', 'sendgo');

        $this->app->singleton(Sendgo::class, function ($app) {
            return new Sendgo([
                'access_key'       => config('sendgo.access_key'),
                'secret_key'       => config('sendgo.secret_key'),
                'kakao_sender_key' => config('sendgo.kakao_sender_key'),
                'sms_sender_key'   => config('sendgo.sms_sender_key'),
                'api_version'      => config('sendgo.api_version', 'v2'),
                'url'              => config('sendgo.url', 'https://sendgo.io'),
            ]);
        });

        // Facade 별칭 바인딩
        $this->app->alias(Sendgo::class, 'sendgo');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sendgo.php' => config_path('sendgo.php'),
            ], 'sendgo-config');
        }
    }

    public function provides(): array
    {
        return [Sendgo::class, 'sendgo'];
    }
}
