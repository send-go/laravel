# sendgo/laravel

> **Laravel에서 카카오 알림톡, 브랜드메시지, SMS를 가장 쉽게 발송하는 공식 Laravel 패키지**

[![Packagist](https://img.shields.io/packagist/v/sendgo/laravel)](https://packagist.org/packages/sendgo/laravel)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

`sendgo/laravel`은 [`sendgo/php`](https://github.com/send-go/php) 코어를 확장한 **Laravel 전용 패키지**입니다.
ServiceProvider 자동 등록, Facade, Config 게시 등 Laravel 통합을 완벽하게 제공합니다.

---

## 목차

- [설치](#설치)
- [빠른 시작](#빠른-시작)
- [Facade 사용법](#facade-사용법)
- [상세 사용법](#상세-사용법)
  - [알림톡](#알림톡)
  - [친구톡](#친구톡)
  - [SMS / LMS / MMS](#sms--lms--mms)
- [서비스 클래스 패턴](#서비스-클래스-패턴)
- [Notification Channel](#notification-channel)
- [Queue / Job 비동기 발송](#queue--job-비동기-발송)
- [예외 처리](#예외-처리)
- [설정 옵션](#설정-옵션)
- [자주 묻는 질문](#자주-묻는-질문-faq)

---

## 설치

```bash
composer require sendgo/laravel
```

Laravel의 패키지 자동 검색(Package Auto-Discovery)으로 ServiceProvider와 Facade가 자동 등록됩니다.

---

## 빠른 시작

### 1단계 — 환경변수 설정 (`.env`)

```env
SENDGO_ACCESS_KEY=your_access_key
SENDGO_SECRET_KEY=your_secret_key
SENDGO_KAKAO_SENDER_KEY=your_kakao_key
SENDGO_SMS_SENDER_KEY=your_sms_key
SENDGO_API_VERSION=v2
```

### 2단계 (선택) — 설정 파일 게시

```bash
php artisan vendor:publish --tag=sendgo-config
```

### 3단계 — 알림톡 전송

```php
<?php

use Sendgo\Php\Sendgo;

class OrderController extends Controller
{
    public function __construct(private Sendgo $sendgo) {}

    public function confirm(Order $order): JsonResponse
    {
        $this->sendgo->alimtalk->send([
            'templateCode' => 'ORDER_CONFIRM_001',
            'contacts'     => [
                [
                    'contact' => $order->user->phone,
                    'name'    => $order->user->name,
                    'var1'    => $order->number,
                    'var2'    => number_format($order->total) . '원',
                ],
            ],
        ]);

        return response()->json(['success' => true]);
    }
}
```

---

## Facade 사용법

```php
<?php

use Sendgo\Laravel\Facades\Sendgo;

// 알림톡 발송
Sendgo::alimtalk()->send([
    'templateCode' => 'ORDER_CONFIRM_001',
    'contacts'     => [['contact' => '01012345678', 'var1' => 'ORD-001']],
]);

// SMS 발송
Sendgo::sms()->sendSms([
    'content'  => '[인증] 인증번호: 123456',
    'contacts' => [['contact' => '01012345678']],
]);
```

---

## 상세 사용법

### 알림톡

```php
<?php

use Sendgo\Php\Sendgo;

// 다건 발송
app(Sendgo::class)->alimtalk->send([
    'templateCode' => 'ORDER_CONFIRM_001',
    'contacts'     => [
        ['contact' => '01011111111', 'name' => '홍길동', 'var1' => 'ORD-001', 'var2' => '29,000원'],
        ['contact' => '01022222222', 'name' => '김철수', 'var1' => 'ORD-002', 'var2' => '15,000원'],
        ['contact' => '01033333333', 'name' => '이영희', 'var1' => 'ORD-003', 'var2' => '52,000원'],
    ],
]);

// 예약 발송
app(Sendgo::class)->alimtalk->send([
    'templateCode' => 'PROMO_SUMMER_2026',
    'scheduleType' => 'SCHEDULED',
    'at'           => '2026-07-28 09:00:00',
    'contacts'     => [['contact' => '01012345678', 'var1' => '여름 한정 50% 할인']],
]);

// SMS 자동 대체 발송
app(Sendgo::class)->alimtalk->send([
    'templateCode' => 'DELIVERY_START_001',
    'replaceSms'   => 'Y',
    'smsSubject'   => '[배송 시작 안내]',
    'smsContent'   => "주문하신 상품이 출고되었습니다.\n송장번호: #{var2}",
    'contacts'     => [['contact' => '01012345678', 'var1' => 'ORD-001', 'var2' => '1234567890']],
]);
```

### 친구톡

> ⚠️ **Deprecated — 친구톡은 카카오 정책에 따라 2025-12-31 종료되었습니다.**
> 2026-01-01 부터 친구톡 발송 요청은 카카오 측에서 **브랜드메시지(자유형)** 로 자동 대체 발송됩니다.
> 호출은 계속 성공하며, 자유 본문 타입(`FT`/`FI`/`FW`)을 개별 수신자에게 보내는 경로는
> 현재 이것뿐이므로 기존 코드를 당장 바꿀 필요는 없습니다.
>
> 다음의 경우에는 **브랜드메시지**를 사용하세요.
> - 템플릿 기반 리치 타입 (`FL`/`FC`/`FM`/`FP`/`FA`)
> - 채널 친구가 **아닌** 수신자 (`targeting` = `N` / `I`)
> - 수신 동의한 전체 채널 친구 동보 (`targeting` = `F`)
>
> 메시지 타입은 1:1 대응되며 변환은 서버가 처리합니다 — `FT`→`BT`, `FI`→`BI`, `FW`→`BW`,
> `FL`→`BL`, `FC`→`BC`, `FM`→`BM`, `FP`→`BP`, `FA`→`BA`.

```php
<?php

// 텍스트형
app(Sendgo::class)->friendtalk->send([
    'content'  => '안녕하세요! 7월 한정 특가 이벤트를 확인해보세요.',
    'contacts' => [['contact' => '01012345678']],
]);

// 이미지형
app(Sendgo::class)->friendtalk->send([
    'messageType' => 'FI',
    'content'     => '이번 주 특가 상품을 확인하세요!',
    'imageUrl'    => 'https://cdn.example.com/banner.jpg',
    'imageLink'   => 'https://example.com/event',
    'contacts'    => [['contact' => '01012345678']],
]);

// 버튼 포함
app(Sendgo::class)->friendtalk->send([
    'content'  => '7월 쿠폰이 도착했습니다! 지금 바로 사용하세요.',
    'buttons'  => [
        ['name' => '쿠폰 받기', 'type' => 'WL', 'linkMo' => 'https://example.com/coupon'],
        ['name' => '고객센터', 'type' => 'WL', 'linkMo' => 'https://example.com/cs'],
    ],
    'contacts' => [['contact' => '01012345678']],
]);
```

### SMS / LMS / MMS

```php
<?php

// SMS (90자 이하)
app(Sendgo::class)->sms->sendSms([
    'content'  => '[Sendgo] 인증번호: 123456 (5분 이내 입력)',
    'contacts' => [['contact' => '01012345678']],
]);

// LMS (장문, 2,000자 이하)
app(Sendgo::class)->sms->sendLms([
    'subject'  => '[중요] 서비스 점검 안내',
    'content'  => "안녕하세요. 서비스 점검이 예정되어 있습니다.\n\n■ 일시: 2026-07-25 02:00 ~ 06:00\n■ 영향: 전체 서비스",
    'contacts' => [['contact' => '01012345678']],
]);

// MMS (이미지 포함)
app(Sendgo::class)->sms->sendMms([
    'subject'  => '[이벤트] 7월 특가',
    'content'  => '이번 달 특가 상품을 확인하세요!',
    'contacts' => [['contact' => '01011111111'], ['contact' => '01022222222']],
]);
```

---

## 서비스 클래스 패턴

```php
<?php
// app/Services/NotificationService.php

namespace App\Services;

use Sendgo\Php\Sendgo;
use Sendgo\Php\Exception\SendgoException;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(private Sendgo $sendgo) {}

    public function sendOrderConfirm(string $phone, string $orderNo, int $amount): void
    {
        $this->sendgo->alimtalk->send([
            'templateCode' => 'ORDER_CONFIRM_001',
            'contacts'     => [
                ['contact' => $phone, 'var1' => $orderNo, 'var2' => number_format($amount) . '원'],
            ],
        ]);
    }

    public function sendShippingAlert(string $phone, string $trackingNo): void
    {
        $this->sendgo->alimtalk->send([
            'templateCode' => 'SHIPPING_001',
            'replaceSms'   => 'Y',
            'smsContent'   => "배송이 시작되었습니다.\n송장번호: {$trackingNo}",
            'contacts'     => [['contact' => $phone, 'var1' => $trackingNo]],
        ]);
    }

    public function sendVerificationCode(string $phone, string $code): void
    {
        try {
            // 알림톡 우선, 실패 시 SMS 대체
            $this->sendgo->alimtalk->send([
                'templateCode' => 'VERIFY_CODE_001',
                'replaceSms'   => 'Y',
                'smsContent'   => "[인증] 인증번호: {$code} (5분 이내 입력)",
                'contacts'     => [['contact' => $phone, 'var1' => $code]],
            ]);
        } catch (SendgoException $e) {
            Log::error('Sendgo 인증번호 발송 실패', [
                'phone'      => $phone,
                'error_code' => $e->getErrorCode(),
                'status'     => $e->getStatusCode(),
            ]);
            throw $e;
        }
    }
}
```

```php
<?php
// app/Providers/AppServiceProvider.php

use App\Services\NotificationService;
use Sendgo\Php\Sendgo;

// ServiceProvider에 이미 Singleton으로 등록되어 있어 자동 주입 가능
$this->app->bind(NotificationService::class, function ($app) {
    return new NotificationService($app->make(Sendgo::class));
});
```

---

## Notification Channel

```php
<?php
// app/Notifications/OrderConfirmedNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Sendgo\Php\Sendgo;

class OrderConfirmedNotification extends Notification
{
    public function __construct(private string $orderNo, private int $amount) {}

    public function via(object $notifiable): array
    {
        return ['sendgo_alimtalk'];
    }

    public function toSendgoAlimtalk(object $notifiable): array
    {
        return [
            'templateCode' => 'ORDER_CONFIRM_001',
            'contacts'     => [
                [
                    'contact' => $notifiable->phone,
                    'var1'    => $this->orderNo,
                    'var2'    => number_format($this->amount) . '원',
                ],
            ],
        ];
    }
}
```

---

## Queue / Job 비동기 발송

```php
<?php
// app/Jobs/SendAlimtalkJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sendgo\Php\Sendgo;
use Sendgo\Php\Exception\SendgoException;
use Illuminate\Support\Facades\Log;

class SendAlimtalkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private string $templateCode,
        private array $contacts,
    ) {}

    public function handle(Sendgo $sendgo): void
    {
        $sendgo->alimtalk->send([
            'templateCode' => $this->templateCode,
            'contacts'     => $this->contacts,
        ]);
    }

    public function failed(SendgoException $e): void
    {
        Log::error('알림톡 발송 실패', [
            'templateCode' => $this->templateCode,
            'error_code'   => $e->getErrorCode(),
        ]);
    }
}

// 디스패치 예시
SendAlimtalkJob::dispatch('ORDER_CONFIRM_001', [
    ['contact' => '01012345678', 'var1' => 'ORD-001'],
])->onQueue('notifications');
```

---

## 예외 처리

```php
<?php

use Sendgo\Php\Exception\SendgoException;

try {
    app(Sendgo::class)->alimtalk->send([...]);
} catch (SendgoException $e) {
    Log::error('Sendgo 발송 실패', [
        'status'     => $e->getStatusCode(),
        'error_code' => $e->getErrorCode(),
        'endpoint'   => $e->getEndpoint(),
    ]);

    match ($e->getErrorCode()) {
        'INVALID_ACCESS_KEY',
        'INVALID_SECRET_KEY'    => alertOps('Sendgo 인증키 오류'),
        'INVALID_TEMPLATE_CODE' => logger()->warning('존재하지 않는 템플릿'),
        'PAYMENT_REQUIRED'      => alertOps('Sendgo 크레딧 부족'),
        'IP_NOT_ALLOWED'        => alertOps('허용되지 않은 IP'),
        default                 => null,
    };
}
```

---

## 설정 옵션

`config/sendgo.php` (vendor:publish 후 커스터마이징 가능):

| 키 | 환경변수 | 기본값 | 설명 |
|----|---------|--------|------|
| `access_key` | `SENDGO_ACCESS_KEY` | — | Sendgo 액세스 키 |
| `secret_key` | `SENDGO_SECRET_KEY` | — | Sendgo 시크릿 키 |
| `kakao_sender_key` | `SENDGO_KAKAO_SENDER_KEY` | `null` | 카카오 발신프로필 키 |
| `sms_sender_key` | `SENDGO_SMS_SENDER_KEY` | `null` | SMS 발신자 키 |
| `api_version` | `SENDGO_API_VERSION` | `'v2'` | API 버전 |
| `url` | `SENDGO_URL` | `'https://sendgo.io'` | API 기본 URL |

---

## 자주 묻는 질문 (FAQ)

**Q. `sendgo/php`와의 차이는 무엇인가요?**
A. `sendgo/php`는 프레임워크 독립적인 순수 PHP 코어 패키지입니다. `sendgo/laravel`은 이를 확장해 ServiceProvider 자동 등록, Facade, .env 설정 바인딩 등 Laravel 통합을 추가합니다.

**Q. Laravel 10, 11, 12 모두 지원하나요?**
A. 네, `illuminate/support` `^10.0|^11.0|^12.0`을 지원합니다.

**Q. Facade를 사용하지 않고 DI로만 쓸 수 있나요?**
A. 네, `Sendgo\Php\Sendgo`를 생성자에서 타입힌트로 주입받아 사용할 수 있습니다.

**Q. 테스트 시 Sendgo를 Mock 처리하려면?**
A. `Sendgo\Php\Sendgo`를 Mockery나 PHPUnit Mock으로 교체하면 됩니다.

---

## 관련 패키지

| 언어/프레임워크 | 패키지 | GitHub |
|----------------|--------|--------|
| PHP (순수) | `sendgo/php` | [php](https://github.com/send-go/php) |
| Spring Boot | `io.sendgo:sendgo-spring` | [spring](https://github.com/send-go/spring) |
| Node.js | `@sendgo/node` | [node](https://github.com/send-go/node) |
| Python | `sendgo-python` | [python](https://github.com/send-go/python) |
| 전체 목록 | — | [send-go GitHub 조직](https://github.com/send-go) |

---

## 브랜드메시지 · 짧은 URL

이 패키지는 코어(`sendgo/php`)의 클라이언트를 그대로 노출하므로, 코어에 있는 채널이
모두 그대로 쓸 수 있습니다. 두 기능 모두 **v2 전용**입니다.

| 기능 | 접근 |
|------|------|
| 카카오 브랜드메시지 (친구톡의 후속 채널) | `Sendgo::brandMessage()` |
| 짧은 URL (단축 + 클릭 반응 분석) | `Sendgo::shortUrl()` |

브랜드메시지는 채널 친구가 아닌 수신자에게도 보낼 수 있고(`targeting` = `N`),
수신 동의한 전체 채널 친구에게 동보 발송할 수도 있습니다(`targeting` = `F`).

짧은 URL 은 메시지 본문의 링크를 줄이고 클릭 반응(일별 추이·디바이스·유입경로·국가)을
집계합니다.

사용 예시와 파라미터는 [코어 README](https://github.com/send-go) 와
[SDK 가이드](https://sendgo.io/ko/sdk) 를 참고하세요.

## 변경 사항

### 1.2.1 (2026-08-14)

- 레지스트리 목록에 노출되는 패키지 설명에서 친구톡을 브랜드메시지로 교체했습니다.
  npm/PyPI/Packagist/Maven/NuGet/RubyGems 검색 결과에 그대로 찍히는 문자열이라
  종료된 채널을 계속 홍보하고 있었습니다.
- 검색 키워드에 `brand-message` 를 추가했습니다 (`friendtalk` 은 유입 검색어라 유지).

### 1.2.0 (2026-08-14)

- **친구톡 Deprecated 표기** — 친구톡은 카카오 정책에 따라 2025-12-31 종료되었고,
  2026-01-01 부터 발송 요청이 브랜드메시지(자유형)로 자동 대체 발송됩니다.
  관련 API 에 각 언어의 표준 deprecation 표기를 달았습니다.
- 자유 본문 타입(`FT`/`FI`/`FW`)의 개별 발송 경로는 아직 친구톡 API 뿐이라는 점을
  문서에 명시했습니다 — 브랜드메시지 API 는 그 조합에 `NOT_A_BRAND_MESSAGE` 를 반환합니다.
- 브랜드메시지 전환 안내와 메시지 타입 1:1 대응표를 README 에 추가했습니다.

### 1.1.0 (2026-08-11)

- 파사드에 `shortUrl()` 문서화 (`@method` 주석) — IDE 자동완성 대응

## 라이선스

MIT License © 2026 [Sendgo](https://sendgo.io)

---

*키워드: 카카오 알림톡 Laravel, 카카오 친구톡 Laravel, SMS 발송 Laravel, 알림톡 Laravel 패키지, Laravel 카카오 API 연동, Laravel Notification Channel, Sendgo Laravel SDK*
