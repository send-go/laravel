<?php

namespace Sendgo\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Sendgo\Php\AlimtalkService;
use Sendgo\Php\BrandMessageService;
use Sendgo\Php\BrandTemplateService;
use Sendgo\Php\FriendtalkService;
use Sendgo\Php\KakaoImageService;
use Sendgo\Php\KakaoSenderService;
use Sendgo\Php\MessageTemplateService;
use Sendgo\Php\NoticeTemplateService;
use Sendgo\Php\RejectedNumberService;
use Sendgo\Php\SenderRegistrationService;
use Sendgo\Php\ShortUrlService;
use Sendgo\Php\SmsService;
use Sendgo\Php\WebhookService;

/**
 * @method static AlimtalkService alimtalk()
 * @method static FriendtalkService friendtalk() Deprecated — 친구톡은 2025-12-31 종료. brandMessage() 를 사용하세요.
 * @method static BrandMessageService brandMessage() 카카오 브랜드메시지 — 친구톡의 후속 채널. v2 전용.
 * @method static SmsService sms()
 * @method static ShortUrlService shortUrl() 짧은 URL — 링크 단축 + 클릭 반응 분석. v2 전용.
 * @method static KakaoSenderService kakaoSenders() 카카오 채널 등록·동기화. v2 전용, 기업 계정 전용.
 * @method static NoticeTemplateService noticeTemplates() 알림톡 템플릿 CRUD·검수 요청. v2 전용, 기업 계정 전용.
 * @method static BrandTemplateService brandTemplates() 브랜드메시지 템플릿 CRUD. v2 전용, 기업 계정 전용.
 * @method static SenderRegistrationService senderRegistration() 발신번호 등록·심사 접수. v2 전용.
 * @method static MessageTemplateService messageTemplates() 문자 상용구 템플릿 CRUD. v2 전용.
 * @method static KakaoImageService kakaoImages() 카카오 이미지 업로드 — 브랜드메시지 템플릿용 URL 발급. v2 전용, 기업 계정 전용.
 * @method static RejectedNumberService rejectedNumbers() 수신거부(080) 번호 조회. v2 전용.
 * @method static WebhookService webhook() 이벤트 웹훅 구독 — 등록·심사 결과를 밀어 받는다. v2 전용.
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
