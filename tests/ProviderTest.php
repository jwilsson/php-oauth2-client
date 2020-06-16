<?php

namespace OAuth2\Tests;

use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use OAuth2\Grant\AuthorizationCode;
use OAuth2\Provider;
use OAuth2\Token;

class ProviderTest extends TestCase
{
    public function testGetAuthenticatedClient()
    {
        $mockClient = new Client();
        $provider = new Provider(
            [],
            $mockClient
        );

        $token = new Token([
            'access_token' => '2YotnFZFEjr1zCsicMWpAA',
        ]);

        $client = $provider->getAuthenticatedClient($token);
        $request = create_request();

        $client->sendRequest($request);

        $request = $mockClient->getLastRequest();

        $this->assertEquals('Bearer 2YotnFZFEjr1zCsicMWpAA', $request->getHeaderLine('Authorization'));
    }

    public function testInitGrant()
    {
        $provider = new Provider([]);

        $grant = $provider->initGrant(AuthorizationCode::class);

        $this->assertInstanceOf(AuthorizationCode::class, $grant);
    }
}
