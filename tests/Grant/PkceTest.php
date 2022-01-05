<?php

namespace OAuth2\Tests\Grant;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use OAuth2\Grant\Pkce;

class PkceTest extends TestCase
{
    protected function setupGrant(array $options = [], ?Client $httpClient = null): Pkce
    {
        $options = array_replace([
            'client_id' => '2bfe9d72a4aae8f06a31025b7536be80',
            'client_secret' => '9d667c2b7fae7a329f32b6df17926154',
            'redirect_uri' => 'https://example.com/callback',
            'endpoints' => [
                // Include a query param to test appending in getAuthorizationUrl()
                'auth_url' => 'https://provider.com/oauth2/auth?grant=pkce',
                'token_url' => 'https://provider.com/oauth2/token',
            ],
        ], $options);

        return new Pkce(
            $options,
            $httpClient ?? new Client(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    public function testGenerateVerifier(): void
    {
        $grant = $this->setupGrant();
        $verifier = $grant->generateVerifier(64);

        $this->assertIsString($verifier);
        $this->assertEquals(strlen($verifier), 64);
    }

    public function testGenerateVerifierInvalidLength(): void
    {
        $grant = $this->setupGrant();

        $this->expectException(\UnexpectedValueException::class);

        $verifier = $grant->generateVerifier(16);
    }

    public function testGetAuthorizationUrl(): void
    {
        $grant = $this->setupGrant();
        $state = $grant->generateState();
        $verifier = 'bb064b23223dff9b805313cdafc355acc64f1642';
        $challenge = '5FOTHWa4QWOwjDt1TV7O19MSuYxfDyjn4PwtG5WbPxg';

        $authorizationUrl = $grant->getAuthorizationUrl($state, $verifier, [
            'custom_param' => 'custom_value',
            'scope' => 'scope-1 scope-2',
        ]);

        $this->assertStringContainsString('client_id=2bfe9d72a4aae8f06a31025b7536be80', $authorizationUrl);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $authorizationUrl);
        $this->assertStringContainsString('response_type=code', $authorizationUrl);
        $this->assertStringContainsString('scope=scope-1%20scope-2', $authorizationUrl);
        $this->assertStringContainsString('state=' . $state, $authorizationUrl);
        $this->assertStringContainsString('https://provider.com/oauth2/auth', $authorizationUrl);
        $this->assertStringContainsString('code_challenge=' . $challenge, $authorizationUrl);
        $this->assertStringContainsString('code_challenge_method=S256', $authorizationUrl);
        $this->assertStringContainsString('custom_param=custom_value', $authorizationUrl);
    }

    public function testRequestAccessToken(): void
    {
        $mockClient = new Client();
        $response = create_response(); // @phpstan-ignore-line

        $mockClient->addResponse($response);

        $code = '5694d08a2e53ffcae0c3103e5ad6f6076abd960eb1f8a56577040bc1028f702b';
        $verifier = 'bb064b23223dff9b805313cdafc355acc64f1642';
        $grant = $this->setupGrant([], $mockClient);
        $token = $grant->requestAccessToken($code, $verifier, [
            'custom_param' => 'custom_value',
        ]);

        $requests = $mockClient->getRequests();
        $body = $requests[0]->getBody()->__toString();

        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertEquals('https://provider.com/oauth2/token', $requests[0]->getUri());

        $this->assertStringContainsString('client_id=2bfe9d72a4aae8f06a31025b7536be80', $body);
        $this->assertStringContainsString('code=' . $code, $body);
        $this->assertStringContainsString('code_verifier=' . $verifier, $body);
        $this->assertStringContainsString('grant_type=authorization_code', $body);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $body);
    }
}
