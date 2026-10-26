<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Exception\SageWebhookVerificationException;
use Dprmc\SageIntacctRestApi\Services\SageWebhookVerifier;
use PHPUnit\Framework\TestCase;

final class SageWebhookVerifierTest extends TestCase
{
    public function test_it_verifies_the_jwt_and_exact_payload_hash(): void
    {
        $secret = 'client-secret';
        $body = '{"event":"updated"}';
        $header = $this->encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = $this->encode([
            'iss' => 'Sage Intacct',
            'exp' => time() + 300,
            'payload_signature' => hash('sha256', $body),
        ]);
        $signature = $this->encodeBinary(hash_hmac('sha256', $header . '.' . $payload, $secret, true));

        $claims = (new SageWebhookVerifier())->verify($body, $header . '.' . $payload . '.' . $signature, $secret);

        self::assertSame('Sage Intacct', $claims['iss']);
    }

    public function test_it_rejects_a_modified_payload(): void
    {
        $this->expectException(SageWebhookVerificationException::class);

        $secret = 'client-secret';
        $body = '{"event":"updated"}';
        $header = $this->encode(['alg' => 'HS256']);
        $payload = $this->encode([
            'iss' => 'Sage Intacct',
            'exp' => time() + 300,
            'payload_signature' => hash('sha256', $body),
        ]);
        $signature = $this->encodeBinary(hash_hmac('sha256', $header . '.' . $payload, $secret, true));

        (new SageWebhookVerifier())->verify('{"event":"tampered"}', $header . '.' . $payload . '.' . $signature, $secret);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(array $payload): string
    {
        return $this->encodeBinary(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function encodeBinary(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
