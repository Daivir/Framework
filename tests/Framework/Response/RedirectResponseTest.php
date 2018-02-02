<?php
namespace Tests\Framework\Response;

use Framework\Response\RedirectResponse;
use PHPUnit\Framework\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testStatus()
    {
        $response = new RedirectResponse('/test');
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testHeader()
    {
        $response = new RedirectResponse('/test');
        $this->assertEquals(['/test'], $response->getHeader('Location'));
    }
}
