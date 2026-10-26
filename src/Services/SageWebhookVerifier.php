<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Services;

use Dprmc\SageIntacctRestApi\Exception\SageWebhookVerificationException;

final class SageWebhookVerifier
{
    /**
     * Verify Sage's JWT signature and the SHA-256 hash of the exact request body.
     *
     * @return array<string, mixed>
     */
    public function verify(string $rawBody, string $signatureHeader, string $clientSecret): array
    {
        $token = $this->extractToken($signatureHeader);
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            throw new SageWebhookVerificationException('The Sage webhook signature is not a JWT.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;
        $header = $this->decodeJsonSegment($encodedHeader, 'header');
        $claims = $this->decodeJsonSegment($encodedPayload, 'claims');

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new SageWebhookVerificationException('The Sage webhook uses an unsupported JWT algorithm.');
        }

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $clientSecret, true),
        );

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            throw new SageWebhookVerificationException('The Sage webhook JWT signature is invalid.');
        }

        $issuer = $claims['iss'] ?? null;

        if ($issuer !== 'Sage Intacct') {
            throw new SageWebhookVerificationException('The Sage webhook issuer is invalid.');
        }

        $expiresAt = $claims['exp'] ?? null;

        if (!is_int($expiresAt) && !is_float($expiresAt) && !is_string($expiresAt)) {
            throw new SageWebhookVerificationException('The Sage webhook is missing an expiration claim.');
        }

        $expiresTimestamp = (int)$expiresAt;

        if ($expiresTimestamp > 20_000_000_000) {
            $expiresTimestamp = (int)floor($expiresTimestamp / 1000);
        }

        if ($expiresTimestamp <= time()) {
            throw new SageWebhookVerificationException('The Sage webhook signature has expired.');
        }

        $expectedPayloadSignature = hash('sha256', $rawBody);

        if (($claims['payload_signature'] ?? null) !== $expectedPayloadSignature) {
            throw new SageWebhookVerificationException('The Sage webhook payload hash is invalid.');
        }

        return $claims;
    }

    /**
     * @return array<string, mixed>
     */
    public function decodeClientContext(string $header): array
    {
        $decoded = base64_decode($header, true);

        if ($decoded === false) {
            throw new SageWebhookVerificationException('The Sage client context is not valid base64.');
        }

        return $this->decodeJson($decoded, 'client context');
    }

    private function extractToken(string $signatureHeader): string
    {
        if (substr_count($signatureHeader, '.') === 2) {
            return $signatureHeader;
        }

        $decoded = base64_decode($signatureHeader, true);

        if ($decoded === false) {
            throw new SageWebhookVerificationException('The Sage webhook signature is not valid base64 or JWT.');
        }

        $payload = $this->decodeJson($decoded, 'signature envelope');
        $token = $payload['jwt'] ?? $payload['token'] ?? null;

        if (!is_string($token) || $token === '') {
            throw new SageWebhookVerificationException('The Sage webhook signature envelope has no JWT.');
        }

        return $token;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonSegment(string $segment, string $name): array
    {
        $decoded = base64_decode(strtr($segment, '-_', '+/') . str_repeat('=', (4 - strlen($segment) % 4) % 4), true);

        if ($decoded === false) {
            throw new SageWebhookVerificationException('The Sage webhook JWT ' . $name . ' is invalid.');
        }

        return $this->decodeJson($decoded, $name);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $contents, string $name): array
    {
        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SageWebhookVerificationException(
                'The Sage webhook ' . $name . ' is not valid JSON.',
                0,
                $exception,
            );
        }

        if (!is_array($payload)) {
            throw new SageWebhookVerificationException('The Sage webhook ' . $name . ' has an invalid shape.');
        }

        return $payload;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
