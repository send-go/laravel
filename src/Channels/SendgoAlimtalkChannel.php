<?php

namespace Sendgo\Laravel\Channels;

/**
 * 카카오 알림톡 알림 채널.
 *
 * ```php
 * public function via(object $notifiable): array
 * {
 *     return ['sendgo_alimtalk'];
 * }
 *
 * public function toSendgoAlimtalk(object $notifiable): array
 * {
 *     return [
 *         'templateCode' => 'ORDER_CONFIRM_001',
 *         'contacts'     => [['contact' => $notifiable->phone, 'var1' => $this->orderNo]],
 *     ];
 * }
 * ```
 */
class SendgoAlimtalkChannel extends SendgoChannel
{
    protected function payloadMethod(): string
    {
        return 'toSendgoAlimtalk';
    }

    protected function dispatch(array $payload): array
    {
        return $this->sendgo->alimtalk->send($payload);
    }
}
