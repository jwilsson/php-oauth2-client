<?php

namespace OAuth2\Tests;

use PHPUnit\Framework\TestCase;
use OAuth2\Token;

class TokenTest extends TestCase
{
    public function testToken(): void
    {
        $token = new Token([
            'access_token' => '2ff2dfe36322448c6953616740a910be57bbd4ca',
            'expires' => 1600000000,
            'refresh_token' => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a',
            'scope' => 'scope-1 scope-2',
            'token_type' => 'example',

            'custom_param' => 'custom_value',
        ]);

        $this->assertEquals($token->getAccessToken(), '2ff2dfe36322448c6953616740a910be57bbd4ca');
        $this->assertEquals($token->getExpires(), 1600000000);
        $this->assertEquals($token->getRefreshToken(), '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a');
        $this->assertEquals($token->getScope(), 'scope-1 scope-2');
        $this->assertEquals($token->getTokenType(), 'example');
        $this->assertEquals($token->getValues(), [
            'custom_param' => 'custom_value',
        ]);
    }

    public function testTokenExpiresIn(): void
    {
        $token = new Token([
            'expires_in' => 3600,
        ]);

        $this->assertEquals($token->getExpires(), time() + 3600);
    }

    public function testJsonSerialize(): void
    {
        $parameters = [
            'access_token' => '2ff2dfe36322448c6953616740a910be57bbd4ca',
            'expires' => 1600000000,
            'refresh_token' => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a',
            'scope' => 'scope-1 scope-2',
            'token_type' => 'example',

            'custom_param' => 'custom_value',
        ];

        $token = new Token($parameters);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($token),
            (string) json_encode($parameters)
        );
    }

    public function testToArray()
    {
        $parameters = [
            'access_token' => '2ff2dfe36322448c6953616740a910be57bbd4ca',
            'expires' => 1600000000,
            'refresh_token' => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a',
            'scope' => 'scope-1 scope-2',
            'token_type' => 'example',

            'custom_param' => 'custom_value',
        ];

        $token = new Token($parameters);

        $this->assertEqualsCanonicalizing($token->toArray(), $parameters);
    }
}
