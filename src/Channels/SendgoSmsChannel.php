<?php

namespace Sendgo\Laravel\Channels;

/**
 * SMS / LMS / MMS 알림 채널. `via: ['sendgo_sms']`, `toSendgoSms()`.
 */
class SendgoSmsChannel extends SendgoChannel
{
    protected function payloadMethod(): string
    {
        return 'toSendgoSms';
    }

    protected function dispatch(array $payload): array
    {
        return $this->sendgo->sms->send($payload);
    }
}
