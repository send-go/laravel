<?php

namespace Sendgo\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Sendgo\Php\AlimtalkService;
use Sendgo\Php\BrandMessageService;
use Sendgo\Php\FriendtalkService;
use Sendgo\Php\ShortUrlService;
use Sendgo\Php\SmsService;

/**
 * @method static AlimtalkService alimtalk()
 * @method static FriendtalkService friendtalk()
 * @method static BrandMessageService brandMessage() 카카오 브랜드메시지 — 친구톡의 후속 채널. v2 전용.
 * @method static SmsService sms()
 * @method static ShortUrlService shortUrl() 짧은 URL — 링크 단축 + 클릭 반응 분석. v2 전용.
 *
 * @see \Sendgo\Php\Sendgo
 */
class Sendgo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sendgo';
    }
}
