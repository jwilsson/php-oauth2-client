<?php

namespace OAuth2\Tests\Grant;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use OAuth2\Grant\ClientCredentials;

class ClientCredentialsTest extends TestCase
{
    protected function setupGrant(
        $options = [],
        $httpClient = null
    ) {
        $options = array_replace([
            'client_id' => '2bfe9d72a4aae8f06a31025b7536be80',
            'client_secret' => '9d667c2b7fae7a329f32b6df17926154',
            'endpoints' => [
                'auth_url' => 'https://provider.com/oauth2/auth',
                'token_url' => 'https://provider.com/oauth2/token',
            ],
        ], $options);

        return new ClientCredentials(
            $options,
            $httpClient ?: new Client(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    public function testRequestAccessToken()
    {
        $mockClient = new Client();
        $response = create_response();

        $mockClient->addResponse($response);

        $grant = $this->setupGrant([], $mockClient);
        $token = $grant->requestAccessToken([
            'custom_param' => 'custom_value',
        ]);

        $requests = $mockClient->getRequests();
        $body = $requests[0]->getBody()->__toString();

        $this->assertEquals($requests[0]->getMethod(), 'POST');
        $this->assertEquals($requests[0]->getUri(), 'https://provider.com/oauth2/token');
        $this->assertEquals(
            $requests[0]->getHeaderLine('Authorization'),
            'Basic MmJmZTlkNzJhNGFhZThmMDZhMzEwMjViNzUzNmJlODA6OWQ2NjdjMmI3ZmFlN2EzMjlmMzJiNmRmMTc5MjYxNTQ='
        );

        $this->assertStringContainsString('grant_type=client_credentials', $body);
        $this->assertStringContainsString('custom_param=custom_value', $body);
    }
}
