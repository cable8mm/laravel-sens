# AGENTS.md - AI Agent Guide for laravel-sens

This file provides context for AI agents working with this Laravel SENS notification package.

## Project Overview

**Package**: `cable8mm/laravel-sens`  
**Namespace**: `Seungmun\Sens`  
**Purpose**: Laravel notification channels for NCLOUD SENS (SMS, LMS, MMS, AlimTalk)  
**PHP**: 8.2+ (8.3+ for Laravel 13)  
**Laravel**: 10, 11, 12, 13

## Architecture

### Core Structure

```
src/
├── Sens.php (Abstract base class)
├── SensServiceProvider.php (Laravel service provider)
├── Contracts/
│   ├── Sens.php (Interface)
│   └── SensMessage.php (Interface)
├── Sms/
│   ├── Sms.php (SMS/LMS/MMS API client)
│   ├── SmsChannel.php (Laravel notification channel)
│   └── SmsMessage.php (Message builder)
├── AlimTalk/
│   ├── AlimTalk.php (AlimTalk API client)
│   ├── AlimTalkChannel.php (Laravel notification channel)
│   └── AlimTalkMessage.php (Message builder)
└── Exceptions/
    └── SensException.php (Custom exceptions)
```

### Design Patterns

1. **Laravel Notification Channel Pattern**
    - Channel classes (`SmsChannel`, `AlimTalkChannel`) implement `send()` method
    - Message classes (`SmsMessage`, `AlimTalkMessage`) implement `toArray()` method
    - Follows Laravel's notification system conventions

2. **Abstract Base Class Pattern**
    - `Sens.php` provides common functionality for API clients
    - Handles authentication, HTTP client, signature generation
    - Child classes: `Sms`, `AlimTalk`

3. **Fluent Interface Pattern**
    - All message builder methods return `static` for method chaining
    - Example: `(new SmsMessage())->to('...')->from('...')->content('...')`

4. **Factory Method Pattern**
    - `SensException` uses static factory methods for different error types
    - `InvalidNCPTokens()`, `apiError()`, `invalidMessage()`, `networkError()`

## Key Classes and Responsibilities

### Sens.php (Abstract Base)

- **Properties**: `$http`, `$serviceId`, `$accessKey`, `$secretKey`, `$config`, `$headers`
- **Key Methods**:
    - `__construct(array $config)` - Initialize with config
    - `assertValidTokens(): bool` - Validate credentials
    - `prepareRequestHeaders(string $method, string $uri): array` - Generate NCP API v2 headers
    - `makeSignature(string $method, string $uri, string $timestamp): string` - Generate HMAC signature
    - `resolveEndpoint(string $uri, array $params): array` - Parse URI template
- **HTTP Client**: Guzzle with 30s timeout, 10s connect timeout

### Sms.php

- **Extends**: `Sens`
- **Constructor**: Sets `alimtalk_service_id` from config
- **send(array $params)**: POST to `/sms/v2/services/{service}/messages`
- **Error Handling**: Logs errors before throwing `SensException`

### SmsMessage.php

- **Properties**: `$type`, `$contentType`, `$countryCode`, `$from`, `$subject`, `$content`, `$messages`, `$files`
- **Key Methods**:
    - `type(string $type)` - SMS, LMS, MMS
    - `contentType(string $type)` - COMM or AD
    - `to(string $to)` - Add recipient (supports multiple)
    - `from(string $from)` - Sender number (dashes removed)
    - `subject(string $subject)` - LMS subject (null filtered in toArray)
    - `file(string $name, mixed $file)` - MMS attachment (max 1MB)
    - `toArray(): array` - Serialize for API
- **Default from**: `config('services.sens.services.sms.sender')`

### AlimTalk.php

- **Extends**: `Sens`
- **Constructor**: Sets `alimtalk_service_id` from config
- **send(array $params)**: POST to `/alimtalk/v2/services/{service}/messages`

### AlimTalkMessage.php

- **Properties**: `$countryCode`, `$to`, `$content`, `$buttons`, `$reserveTime`, `$reserveTimeZone`, `$scheduleCode`, `$templateCode`, `$plusFriendId`
- **Key Methods**:
    - `templateCode(string $code)` - Required, validates non-empty
    - `addButton(array $button)` - Add interactive button
    - `setReserved(string $time, string $tz)` - Schedule message
    - `setSchedule(string $code)` - Set schedule code
    - `plusFriendId(string $id)` - Override default
    - `toArray(): array` - Serialize for API
- **Default plusFriendId**: `config('laravel-sens.plus_friend_id')`

### SensException.php

- **Extends**: `Exception`
- **Factory Methods**:
    - `InvalidNCPTokens(string $message)` - Missing/invalid credentials
    - `apiError(string $message, int $statusCode)` - API returned error
    - `invalidMessage(string $message)` - Invalid message data
    - `networkError(string $message)` - Network/connection error

## Configuration

### Environment Variables

```env
SENS_ACCESS_KEY=your-access-key
SENS_SECRET_KEY=your-secret-key
SENS_SERVICE_ID=sms-service-id
SENS_ALIMTALK_SERVICE_ID=alimtalk-service-id
SENS_PLUS_FRIEND_ID=@your-plus-friend-id
```

### Config File

- Published to: `config/laravel-sens.php`
- Keys: `service_id`, `alimtalk_service_id`, `plus_friend_id`, `access_key`, `secret_key`

## API Details

### NCP SENS API v2 Authentication

1. **Headers Required**:
    - `Content-Type: application/json; charset=utf-8`
    - `x-ncp-apigw-timestamp`: Current timestamp (milliseconds)
    - `x-ncp-iam-access-key`: Access key
    - `x-ncp-apigw-signature-v2`: Base64-encoded HMAC-SHA256 signature

2. **Signature Generation**:

    ```
    Buffer:
    - POST {uri}
    - {timestamp}
    - {access_key}

    Signature = base64(hmac_sha256(buffer, secret_key))
    ```

### SMS API Endpoint

- URL: `https://sens.apigw.ntruss.com/sms/v2/services/{service_id}/messages`
- Method: POST
- Body: JSON with type, contentType, countryCode, from, content, messages[]

### AlimTalk API Endpoint

- URL: `https://sens.apigw.ntruss.com/alimtalk/v2/services/{service_id}/messages`
- Method: POST
- Body: JSON with plusFriendId, templateCode, messages[]

## Testing Strategy

### Test Structure

```
tests/
├── TestCase.php (Base test case with Mockery cleanup)
├── SensTest.php (Signature generation)
├── SmsTest.php (SMS API client with Guzzle mocks)
├── AlimTalkTest.php (AlimTalk API client with Guzzle mocks)
├── SmsMessageTest.php (SMS message serialization)
├── SmsMessageEdgeCaseTest.php (Edge cases)
├── AlimTalkMessageTest.php (AlimTalk message serialization)
├── AlimTalkMessageSetReservedTest.php (Scheduling)
├── SensServiceProviderTest.php (DI and config)
├── SensSmsChannelTest.php (SMS channel integration)
└── SensAlimTalkChannelTest.php (AlimTalk channel integration)
```

### Test Patterns

1. **API Client Tests** (SmsTest, AlimTalkTest)
    - Use Guzzle MockHandler to simulate API responses
    - Inject mock client via reflection
    - Test success (200), API errors (500), invalid tokens

2. **Message Tests** (SmsMessageTest, AlimTalkMessageTest)
    - Test serialization to array
    - Test default values
    - Test file attachments
    - Test edge cases (null filtering, size limits)

3. **Channel Tests** (SensSmsChannelTest, SensAlimTalkChannelTest)
    - Mock Sms/AlimTalk service
    - Verify `send()` receives correct payload
    - Use Mockery for mocking

4. **Token Validation Tests** (SensAssertValidTokensTest)
    - Test all combinations of valid/invalid tokens
    - 5 test cases covering all scenarios

### Running Tests

```bash
composer test
```

**Current Status**: 31 tests, 48 assertions, 0 failures, 0 risky

## Coding Conventions

### PHP Standards

- PSR-4 autoloading
- Strict types (implicit in PHP 8.2+)
- Return type hints on all methods
- Visibility declarations (public/protected/private)

### Naming Conventions

- Classes: PascalCase
- Methods: camelCase
- Properties: camelCase
- Constants: UPPER_SNAKE_CASE

### Method Chaining

- All builder methods return `static`
- Enable fluent interface pattern

### Error Handling

- Throw `SensException` for all API errors
- Log errors before throwing (use `Log::error()`)
- Include context in logs: message, exception class

### Null Handling

- `SmsMessage::toArray()`: Filter out null `subject`
- `AlimTalkMessage::toArray()`: Conditionally include `reserveTime`, `reserveTimeZone`, `scheduleCode`

## Important Implementation Details

### Phone Number Formatting

- Dashes removed automatically: `010-1234-5678` → `01012345678`
- Applied in `SmsMessage::from()` and `SmsMessage::to()`

### File Attachments (MMS)

- Max size: 1MB (1024 \* 1024 bytes)
- Supported formats: UploadedFile or file path string
- Encoding: Base64
- Throws `FileNotFoundException` if invalid or too large

### Template Code Validation

- `AlimTalkMessage::templateCode()` validates non-empty
- Throws `InvalidArgumentException` if empty

### Multiple Recipients

- SMS: Chain `to()` method multiple times
- AlimTalk: Currently single recipient per message (API limitation)

### Service Provider

- Auto-discovery enabled in composer.json
- `register()`: Bind Sms and AlimTalk to container
- `boot()`: Publish config
- `provides()`: Return provided services

## Known Limitations

1. **AlimTalk Single Recipient**: API only supports one recipient per message
2. **No Retry Logic**: Failed requests are not automatically retried
3. **No Queue Integration**: Notifications sent synchronously (Laravel queue can be added by user)
4. **No Rate Limiting**: User must implement their own rate limiting
5. **No Webhook Support**: Delivery receipts not handled

## Migration from seungmun/laravel-sens

1. Update composer:

    ```bash
    composer remove seungmun/laravel-sens
    composer require cable8mm/laravel-sens
    ```

2. No code changes needed - namespace and API are identical

3. Optional: Update `.env` variable name from `SENS_PlUS_FRIEND_ID` to `SENS_PLUS_FRIEND_ID`

## Common Pitfalls for AI Agents

1. **Don't modify the signature generation logic** - It's NCP API v2 specific and must not change
2. **Don't remove null filtering** - API rejects null values
3. **Don't add retry logic without discussion** - Could cause duplicate messages
4. **Don't change namespace** - `Seungmun\Sens` is for backward compatibility
5. **Don't forget to add tests** - All changes require corresponding tests
6. **Don't use `\Mockery::reset()`** - Use `Mockery::close()` in tearDown
7. **Don't hardcode API URLs** - Use `resolveEndpoint()` method

## When Making Changes

1. **Always run tests**: `composer test`
2. **Add tests for new features**: Follow existing patterns
3. **Update README if user-facing**: Add examples for new features
4. **Update this file if architecture changes**: Keep AGENTS.md current
5. **Check backward compatibility**: This is a maintained fork

## Dependencies

- `guzzlehttp/guzzle`: ^6.0|^7.0 (HTTP client)
- `illuminate/support`: ^10.0|^11.0|^12.0|^13.0 (Laravel framework)
- `illuminate/notifications`: ^10.0|^11.0|^12.0|^13.0 (Laravel notifications)
- `mockery/mockery`: ^1.0 (Testing)
- `orchestra/testbench`: ^8.0|^9.0|^10.0|^11.0 (Laravel package testing)

## File References

- **Main config**: `config/laravel-sens.php`
- **Service provider**: `src/SensServiceProvider.php`
- **Base class**: `src/Sens.php`
- **Exception class**: `src/Exceptions/SensException.php`
- **Test base**: `tests/TestCase.php`
