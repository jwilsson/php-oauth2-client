<?php

namespace OAuth2\Tests\Grant;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use OAuth2\Grant\AuthorizationCode;
use OAuth2\Grant\Exception\GrantException;

class AuthorizationCodeTest extends TestCase
{
    protected function setupGrant(
        $options = [],
        $httpClient = null
    ) {
        $options = array_replace([
            'client_id' => '2bfe9d72a4aae8f06a31025b7536be80',
            'client_secret' => '9d667c2b7fae7a329f32b6df17926154',
            'redirect_uri' => 'https://example.com/callback',
            'endpoints' => [
                'auth_url' => 'https://provider.com/oauth2/auth',
                'token_url' => 'https://provider.com/oauth2/token',
            ],
        ], $options);

        return new AuthorizationCode(
            $options,
            $httpClient ?: new Client(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    public function testGetAuthorizationUrl()
    {
        $grant = $this->setupGrant();
        $state = $grant->generateState();

        $authorizationUrl = $grant->getAuthorizationUrl($state, [
            'custom_param' => 'custom_value',
            'scope' => 'scope-1 scope-2',
        ]);

        $this->assertStringContainsString('client_id=2bfe9d72a4aae8f06a31025b7536be80', $authorizationUrl);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $authorizationUrl);
        $this->assertStringContainsString('response_type=code', $authorizationUrl);
        $this->assertStringContainsString('scope=scope-1+scope-2', $authorizationUrl);
        $this->assertStringContainsString('state=' . $state, $authorizationUrl);
        $this->assertStringContainsString('https://provider.com/oauth2/auth', $authorizationUrl);
        $this->assertStringContainsString('custom_param=custom_value', $authorizationUrl);
    }

    public function testRequestAccessToken()
    {
        $mockClient = new Client();
        $response = create_response();

        $mockClient->addResponse($response);

        $code = '5694d08a2e53ffcae0c3103e5ad6f6076abd960eb1f8a56577040bc1028f702b';
        $grant = $this->setupGrant([], $mockClient);
        $token = $grant->requestAccessToken($code, [
            'custom_param' => 'custom_value',
        ]);

        $request = $mockClient->getLastRequest();
        $body = $request->getBody()->__toString();

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://provider.com/oauth2/token', $request->getUri());

        $this->assertStringContainsString('client_id=2bfe9d72a4aae8f06a31025b7536be80', $body);
        $this->assertStringContainsString('client_secret=9d667c2b7fae7a329f32b6df17926154', $body);
        $this->assertStringContainsString('code=' . $code, $body);
        $this->assertStringContainsString('grant_type=authorization_code', $body);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $body);
    }

    public function testRequestAccessTokenError()
    {
        $mockClient = new Client();
        $response = create_response(400, [
            'error' => 'Invalid request',
        ]);

        $mockClient->addResponse($response);

        $code = '5694d08a2e53ffcae0c3103e5ad6f6076abd960eb1f8a56577040bc1028f702b';
        $grant = $this->setupGrant([], $mockClient);

        $this->expectException(GrantException::class);

        $grant->requestAccessToken($code, [
            'custom_param' => 'custom_value',
        ]);
    }

    public function testRequestAccessTokenNoToken()
    {
        $mockClient = new Client();
        $response = create_response(200, [
            'access_token' => null,
        ]);

        $mockClient->addResponse($response);

        $code = '5694d08a2e53ffcae0c3103e5ad6f6076abd960eb1f8a56577040bc1028f702b';
        $grant = $this->setupGrant([], $mockClient);

        $this->expectException(GrantException::class);

        $grant->requestAccessToken($code, [
            'custom_param' => 'custom_value',
        ]);
    }
}
