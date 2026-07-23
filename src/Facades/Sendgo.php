<?php

namespace Sendgo\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Sendgo\Php\AlimtalkService;
use Sendgo\Php\FriendtalkService;
use Sendgo\Php\SmsService;

/**
 * @method static AlimtalkService alimtalk()
 * @method static FriendtalkService friendtalk()
 * @method static SmsService sms()
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
