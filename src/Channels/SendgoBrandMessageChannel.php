<?php

namespace Sendgo\Laravel\Channels;

/**
 * 카카오 브랜드메시지 알림 채널. `via: ['sendgo_brand_message']`,
 * `toSendgoBrandMessage()`. v2 전용이다.
 */
class SendgoBrandMessageChannel extends SendgoChannel
{
    protected function payloadMethod(): string
    {
        return 'toSendgoBrandMessage';
    }

    protected function dispatch(array $payload): array
    {
        return $this->sendgo->brandMessage->send($payload);
    }
}
