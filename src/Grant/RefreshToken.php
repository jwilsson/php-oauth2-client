<?php

declare(strict_types=1);

namespace OAuth2\Grant;

use OAuth2\Grant;
use OAuth2\Token;

class RefreshToken extends Grant
{
    /**
     * Request an access token from the authorization server.
     *
     * @param string $refreshToken A previously received refresh token.
     * @param array $parameters Optional. Additional parameters to pass to the authorization server.
     *
     * @return OAuth2\Token
     */
    public function requestAccessToken(string $refreshToken, array $parameters = []): Token
    {
        $parameters = array_replace([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ], $parameters);

        $body = http_build_query($parameters, '', '&');
        $body = $this->streamFactory->createStream($body);
        $headerValue = base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']);

        $request = $this->createTokenRequest($parameters);
        $request = $request->withAddedHeader('Authorization', 'Basic ' . $headerValue);
        $request = $request->withBody($body);

        return $this->sendTokenRequest($request);
    }
}