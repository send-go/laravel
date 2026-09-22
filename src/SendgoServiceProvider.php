<?php

namespace Sendgo\Laravel;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Sendgo\Laravel\Channels\SendgoAlimtalkChannel;
use Sendgo\Laravel\Channels\SendgoBrandMessageChannel;
use Sendgo\Laravel\Channels\SendgoSmsChannel;
use Sendgo\Php\Sendgo;
use Sendgo\Php\AccountClient;

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

        $this->app->singleton(AccountClient::class, fn () => new AccountClient(
            (string) config('sendgo.agent_token', ''),
            config('sendgo.url', 'https://sendgo.io'),
        ));

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

        $this->registerNotificationChannels();
    }

    /**
     * `via: ['sendgo_alimtalk']` 을 실제로 동작하게 만든다.
     *
     * README 는 이 채널을 계속 예시로 실었는데 등록이 없어서, 문서대로 따라 한
     * 사람은 "Driver [sendgo_alimtalk] not supported." 를 받았다.
     */
    protected function registerNotificationChannels(): void
    {
        $channels = [
            'sendgo_alimtalk' => SendgoAlimtalkChannel::class,
            'sendgo_sms' => SendgoSmsChannel::class,
            'sendgo_brand_message' => SendgoBrandMessageChannel::class,
        ];

        foreach ($channels as $name => $channel) {
            Notification::extend($name, fn ($app) => new $channel($app->make(Sendgo::class)));
        }
    }

    public function provides(): array
    {
        return [Sendgo::class, AccountClient::class, 'sendgo'];
    }
}
