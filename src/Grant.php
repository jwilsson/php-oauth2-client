<?php

declare(strict_types=1);

namespace OAuth2;

use OAuth2\Grant\Exception\GrantException;
use OAuth2\Token;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

abstract class Grant
{
    protected array $options = [];

    protected ClientInterface $httpClient;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

    /**
     * Constructor, set options and instantiate common classes.
     *
     * @param array $options Options for the provider to use.
     * @param Psr\Http\Client\ClientInterface|null $httpClient A PSR-18 compatible HTTP client to use.
     * @param Psr\Http\Message\RequestFactoryInterface|null $requestFactory A PSR-17 compatible request factory to use.
     * @param Psr\Http\Message\StreamFactoryInterface|null $streamFactory A PSR-17 compatible stream factory to use.
     */
    public function __construct(
        array $options,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->options = $options;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Create an authorization URL.
     *
     * @param string $state A random, secret value used to protect aginst CSRF attacks.
     * @param array $parameters Parameters to include in the authorization URL.
     *
     * @return string
     */
    protected function createAuthorizationUrl(string $state, array $parameters = []): string
    {
        $parameters = array_replace([
            'client_id' => $this->options['client_id'],
            'redirect_uri' => $this->options['redirect_uri'] ?: null,
            'response_type' => 'code',
            'scope' => null,
            'state' => $state,
        ], $parameters);

        $url = $this->options['endpoints']['auth_url'];

        if (strpos($url, '?') !== false) {
            $url = $url . '&';
        } else {
            $url = $url . '?';
        }

        return $url . http_build_query($parameters, '', '&');
    }

    /**
     * Create a new token request instance.
     *
     * @param array $parameters Parameters to pass to the authorization server.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    protected function createTokenRequest(array $parameters): RequestInterface
    {
        $body = http_build_query($parameters, '', '&');
        $body = $this->streamFactory->createStream($body);

        $request = $this->requestFactory->createRequest('POST', $this->options['endpoints']['token_url']);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withBody($body);

        return $request;
    }

    /**
     * Generate a random state value.
     *
     * @param int $length Length of the state value.
     *
     * @return string
     */
    public function generateState($length = 32): string
    {
        // Length will be doubled when converting to hex
        return bin2hex(
            random_bytes($length / 2)
        );
    }

    /**
     * Send a previously instantiated token request.
     *
     * @param Psr\Http\Message\RequestInterface The token request object.
     *
     * @throws OAuth2\Grant\Exception\GrantException
     *
     * @return OAuth2\Token
     */
    protected function sendTokenRequest($request): Token
    {
        $response = $this->httpClient->sendRequest($request);
        $body = $response->getBody()->__toString();
        $body = json_decode($body, true);

        if (isset($body['error'])) {
            $message = $body['error_description'] ?? $body['error'];

            throw new GrantException($message, $response->getStatusCode(), $response);
        } elseif (!isset($body['access_token'])) {
            $message = 'No access token present in response.';

            throw new GrantException($message, $response->getStatusCode(), $response);
        }

        return new Token($body);
    }
}
