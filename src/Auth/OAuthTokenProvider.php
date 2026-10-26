<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Auth;

use Dprmc\SageIntacctRestApi\Contracts\TokenStore;
use Dprmc\SageIntacctRestApi\Exception\SageIntacctAuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class OAuthTokenProvider
{
    private readonly TokenStore $tokenStore;

    private readonly ClientInterface $httpClient;

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly SageOAuthConfig $config,
        ?TokenStore $tokenStore = null,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->tokenStore = $tokenStore ?? new InMemoryTokenStore();
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getAccessToken(bool $forceRefresh = false): string
    {
        $storedToken = $this->tokenStore->get();

        if (!$forceRefresh && $storedToken !== null && !$storedToken->isExpired()) {
            return $storedToken->accessToken;
        }

        if ($storedToken?->refreshToken !== null && $this->config->grantType !== 'client_credentials') {
            $token = $this->requestToken([
                'grant_type' => 'refresh_token',
                'refresh_token' => $storedToken->refreshToken,
            ]);

            return $token->accessToken;
        }

        $tokenRequest = [
            'grant_type' => $this->config->grantType,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
        ];

        if ($this->config->username !== null) {
            $tokenRequest['username'] = $this->config->username;
        }

        if ($this->config->scope !== null) {
            $tokenRequest['scope'] = $this->config->scope;
        }

        if ($this->config->redirectUri !== null) {
            $tokenRequest['redirect_uri'] = $this->config->redirectUri;
        }

        $token = $this->requestToken($tokenRequest);

        return $token->accessToken;
    }

    public function exchangeAuthorizationCode(string $authorizationCode): OAuthToken
    {
        $tokenRequest = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'code' => $authorizationCode,
        ];

        if ($this->config->redirectUri !== null) {
            $tokenRequest['redirect_uri'] = $this->config->redirectUri;
        }

        return $this->requestToken($tokenRequest);
    }

    public function forget(): void
    {
        $this->tokenStore->forget();
    }

    /**
     * @param array<string, string> $formParameters
     */
    private function requestToken(array $formParameters): OAuthToken
    {
        try {
            $response = $this->httpClient->request('POST', $this->config->tokenEndpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $formParameters,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $exception) {
            throw new SageIntacctAuthenticationException(
                'The Sage OAuth token request failed before a response was received.',
                0,
                $exception,
            );
        }

        $payload = $this->decodeResponse($response);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logger->warning('Sage OAuth token request was rejected.', [
                'status' => $response->getStatusCode(),
            ]);

            throw new SageIntacctAuthenticationException(
                'Sage rejected the OAuth token request.',
                $response->getStatusCode(),
            );
        }

        try {
            $token = OAuthToken::fromResponse($payload);
        } catch (\InvalidArgumentException $exception) {
            throw new SageIntacctAuthenticationException(
                'Sage returned an invalid OAuth token response.',
                $response->getStatusCode(),
                $exception,
            );
        }

        $this->tokenStore->put($token);

        return $token;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        $contents = (string)$response->getBody();

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SageIntacctAuthenticationException(
                'Sage returned a non-JSON OAuth response.',
                $response->getStatusCode(),
                $exception,
            );
        }

        if (!is_array($payload)) {
            throw new SageIntacctAuthenticationException(
                'Sage returned an unexpected OAuth response shape.',
                $response->getStatusCode(),
            );
        }

        return $payload;
    }
}
