<?php

namespace OAuth2\Tests\Grant\Exception;

use PHPUnit\Framework\TestCase;
use OAuth2\Grant\Exception\GrantException;

class GrantExceptionTest extends TestCase
{
    public function testGetResponse(): void
    {
        $response = create_response(400); // @phpstan-ignore-line

        $exception = new GrantException('Invalid request', 400, $response);

        $this->assertSame($response, $exception->getResponse());
    }
}
