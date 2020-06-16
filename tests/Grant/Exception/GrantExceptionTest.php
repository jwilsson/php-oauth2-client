<?php

namespace OAuth2\Tests\Grant\Exception;

use PHPUnit\Framework\TestCase;
use OAuth2\Grant\Exception\GrantException;

class GrantExceptionTest extends TestCase
{
    public function testGetResponse()
    {
        $response = create_response(400);

        $exception = new GrantException('Invalid request', 400, $response);

        $this->assertSame($response, $exception->getResponse());
    }
}
