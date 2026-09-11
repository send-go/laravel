<?php

namespace Sendgo\Laravel\Channels;

use Illuminate\Notifications\Notification;
use Sendgo\Php\Sendgo;

/**
 * Sendgo 알림 채널의 공통 뼈대.
 *
 * README 는 처음부터 `via: ['sendgo_alimtalk']` 예시를 실었지만 채널이 실제로
 * 등록되지는 않아, 문서대로 따라 하면 "Driver [sendgo_alimtalk] not
 * supported." 로 죽었다. 이 클래스와 SendgoServiceProvider 의 등록이 그
 * 간극을 메운다.
 *
 * 각 채널은 알림 객체에서 `to{채널}` 메서드를 찾아 그 반환 배열을 그대로
 * 해당 서비스의 `send()` 에 넘긴다. SDK 의 페이로드 형태를 그대로 쓰므로
 * 별도의 메시지 빌더를 배울 필요가 없다.
 */
abstract class SendgoChannel
{
    public function __construct(protected Sendgo $sendgo) {}

    /**
     * 알림 객체에서 찾을 메서드 이름 (예: `toSendgoAlimtalk`).
     */
    abstract protected function payloadMethod(): string;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    abstract protected function dispatch(array $payload): array;

    /**
     * @return array<string, mixed>|null  알림이 이 채널용 페이로드를 만들지 않으면 null
     */
    public function send(object $notifiable, Notification $notification): ?array
    {
        $method = $this->payloadMethod();

        if (! method_exists($notification, $method)) {
            return null;
        }

        $payload = $notification->{$method}($notifiable);

        // 조건부로 발송을 건너뛰고 싶을 때 알림이 null 을 돌려줄 수 있게 둔다.
        if (! is_array($payload) || $payload === []) {
            return null;
        }

        return $this->dispatch($payload);
    }
}
