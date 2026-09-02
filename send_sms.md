# Send SMS API Integration Guide & Standards

This document outlines how to integrate SMS sending functionality into your application using the Skypush / RehoPush API. It incorporates critical lessons learned from production integrations (`lodge` & `pms` workspaces) to prevent common gateway errors such as `Undefined variable $params` and `Message not match with template.`.

---

## Overview

- **Base URL**: `https://pushsms.rehospace.com`  
- **Endpoint**: `/api/v1/send`  
- **Method**: `POST`

---

## Authentication

All API requests require an API Key to be passed in the headers.

- **Header Name**: `X-API-KEY`  
- **Value**: *Your Internal API Key* (Contact System Administrator to obtain this)

---

## Request Specification

### Headers
| Header | Value | Required | Notes |
|--------|-------|----------|-------|
| `Content-Type` | `application/json` | Yes | **CRITICAL**: Must be explicitly declared. Sending without `application/json` causes remote `json_decode` failures (`Exception: Undefined variable $params`). |
| `Accept` | `application/json` | Yes | Ensures JSON responses from the gateway. |
| `X-API-KEY` | `<YOUR_SECRET_KEY>` | Yes | Your secret API key. |

### Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `to` | String | Yes | Comma-separated phone numbers in strict E.164 format without plus (e.g., `2557xxxx,2557xxxx`). See **Phone Number Normalization** below. |
| `message` | String | Yes | The SMS content. Max 1000 chars. Must adhere to whitelisted template rules if DLT/template enforcement is active. |
| `sender` | String | No | Custom Sender ID (max 11 chars). Defaults to system default if omitted. |
| `sender_id` | String | **Yes** (when using custom sender) | **CRITICAL**: Must be passed right alongside `sender` (`"sender_id": "SKYLINK"`). The remote API controller explicitly expects `sender_id` to initialize internal parameter mapping without throwing `Exception: Undefined variable $params`. |
| `client_app` | String | No | Identifier for the calling application (e.g. `HMS`, `CRM`, or `1`). |
| `reference` | String | No | Unique internal reference ID for tracking (e.g., `sms_668f...`). Highly recommended for status reporting in the push SMS report app. |
| `language` | String | No | `English` (default 7-bit ASCII) or `Unicode` (when special characters or non-ASCII symbols are included). |
| `scheduled_at` | String | No | Date/Time to schedule the message (format: `YYYY-MM-DD HH:mm:ss`). |

---

## Critical Integration Rules & Best Practices

### 1. Phone Number Normalization (`formatPhoneNumber`)
Do **not** send raw local numbers (`07...`, `06...`) or numbers with leading plus signs (`+255...`). Passing unnormalized numbers can cause carrier route mismatches or template rejection (`0,074...,Message not match with template.`).

Always run every phone number through a 4-step normalization routine:
1. Strip all non-numeric characters (`+`, `-`, spaces, parentheses).
2. If the number starts with `0` (`07...`, `06...`), replace `0` with `255` (`2557...`, `2556...`).
3. If the number starts with `2550...` (common user entry typo), strip the extra `0` after `255`.
4. If the stripped number is exactly 9 digits (`7xxxx...`), prepend `255`.

### 2. The `sender_id` Parameter Mandate
To ensure compatibility across all endpoints of `pushsms.rehospace.com` (/ RehoPush / Skypush), **always pass both `sender` and `sender_id` simultaneously** inside your JSON payload when specifying a custom sender:
```json
{
    "sender": "SKYLINK",
    "sender_id": "SKYLINK"
}
```
*Why?* If only `sender` is provided, some versions of the remote API controller fail to trigger parameter initialization, falling through to where `$params` is undefined (`Exception: Undefined variable $params`).

### 3. Template Whitelisting & Character Encoding Standards
If you receive the error response:
```text
0,0742177328,Message not match with template.
```
This indicates that the message reached the gateway/carrier template validation engine and was blocked because:
- **Unregistered Text**: The text does not exactly match a pre-approved template registered under your Sender ID in the Skypush portal.
- **Unicode/Special Characters**: Characters such as the En-Dash (`–` `U+2013`), smart quotes (`“`), emojis (`🎉`), or unescaped line breaks (`\n`) break standard ASCII template matching. Always use standard ASCII hyphens (`-`) and clean text when sending under standard `English` encoding. If special characters are strictly required, you must pass `"language": "Unicode"` and ensure the template is whitelisted as Unicode on the portal.

---

## Proven Implementation Reference (Laravel / PHP)

### Complete Service Class Pattern (`App\Services\SmsService`)
Below is the exact, battle-tested implementation proven across both `pms` and `lodge` workspaces:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function send(string $to, string $message): bool
    {
        $to = trim($to);

        // 1. Normalize phone number(s) to 2557xxxx format
        $numbers = array_map([self::class, 'formatPhoneNumber'], explode(',', $to));
        $normalizedTo = implode(',', array_filter($numbers));

        if (empty($normalizedTo) || empty($message)) {
            Log::warning('SmsService: skipped - empty recipient or message.');
            return false;
        }

        $baseUrl = rtrim(config('services.pushsms.url', 'https://pushsms.rehospace.com'), '/');
        $apiKey  = config('services.pushsms.api_key', '');
        $sender  = config('services.pushsms.sender', '');

        try {
            // 2. Construct payload with both sender and sender_id
            $payload = [
                'to'         => $normalizedTo,
                'message'    => $message,
                'client_app' => config('services.pushsms.client_app', 'HMS'),
                'reference'  => 'sms_' . uniqid(),
            ];

            if (!empty($sender)) {
                $payload['sender']    = $sender;
                $payload['sender_id'] = $sender; // Required by remote API controller
            }

            // 3. Explicitly enforce application/json headers
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-API-KEY'    => $apiKey,
            ])->post($baseUrl . '/api/v1/send', $payload);

            if ($response->successful()) {
                Log::info("SmsService: OK to [{$normalizedTo}], id=" . ($response->json('id') ?? 'n/a'));
                return true;
            }

            Log::error("SmsService: failed to [{$normalizedTo}]. HTTP {$response->status()}: {$response->body()}");
            return false;

        } catch (\Throwable $e) {
            Log::error("SmsService: exception - {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Standard 4-step Phone Number Normalizer
     */
    private static function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', trim($phone));

        if (substr($phone, 0, 1) === '0') {
            $phone = '255' . substr($phone, 1);
        }

        if (substr($phone, 0, 4) === '2550') {
            $phone = '255' . substr($phone, 4);
        }

        if (strlen($phone) === 9) {
            $phone = '255' . $phone;
        }

        return $phone;
    }
}
```

---

## Response Specification

### Success Response (201 Created / 200 OK)
```json
{
    "status": "ok",
    "id": 154
}
```

### Error Response (422 Unprocessable Entity)
Occurs when validation fails (e.g., missing `to`, `message`, or `sender_id`).
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "to": [
            "The to field is required."
        ]
    }
}
```

### Gateway Status / Push SMS Report App Errors
If recorded in the push SMS status report or callback:
- `Exception: Undefined variable $params`: Caused by omitting `sender_id` alongside `sender` or sending requests without `Content-Type: application/json`.
- `0,0742177328,Message not match with template.`: Caused by sending unnormalized numbers (`07...`), non-ASCII characters (`–`), or message content that has not been whitelisted in the Skypush portal under your specific Sender ID.

### Error Response (401 Unauthorized)
Occurs when the `X-API-KEY` header is missing or incorrect.
```json
{
    "message": "Unauthorized"
}
```

---

## Additional Endpoints

### Check Balance
**Endpoint**: `GET /api/v1/balance`  
Returns the local remaining SMS balance for a specific sender account or client application.

> [!IMPORTANT]
> To prevent platform-wide balance exposure, this endpoint strictly requires you to identify your sender profile. You must specify your sender or client application name using one of the following methods.

#### Identification Methods
You can pass your identifier through any of the following fields (searched in order of priority):
- **Query Parameter**: `sender`, `sender_id`, or `client_app`
- **Request Header**: `X-Client-App` or `X-Sender-Id`

#### Example Request (Query Parameter)
```bash
curl -X GET "https://pushsms.rehospace.com/api/v1/balance?sender=SKYLINK" \
  -H "Accept: application/json" \
  -H "X-API-KEY: your_super_secret_key"
```

#### Example Request (Header)
```bash
curl -X GET "https://pushsms.rehospace.com/api/v1/balance" \
  -H "Accept: application/json" \
  -H "X-API-KEY: your_super_secret_key" \
  -H "X-Client-App: SKYLINK"
```

#### Successful Response (200 OK)
```json
{
    "status": "success",
    "sender": "SKYLINK",
    "balance": 750
}
```

#### Error Response - Missing Identifier (400 Bad Request)
Occurs when the request does not provide any of the required query parameters or headers.
```json
{
    "status": "error",
    "message": "Missing sender identifier. Please specify your sender name or ID using the \"sender\" query parameter or \"X-Client-App\" header."
}
```

#### Error Response - Sender Not Found (404 Not Found)
Occurs when the specified sender identifier is not registered or does not have an initialized balance.
```json
{
    "status": "error",
    "message": "Sender balance not found for identifier: SKYLINK"
}
```

---

### Check Sender Balance (Mobishastra Provider Live Balance)
**Endpoint**: `GET /api/v1/sender-balance?username={username}`  
Queries the live Mobishastra API for the provider balance of a specific sub-account username.

#### Request Parameters
- `username` (String, Required): The Mobishastra sub-account profile username.

#### Successful Response (200 OK)
```json
{
    "balance": "balance = 5000"
}
```
