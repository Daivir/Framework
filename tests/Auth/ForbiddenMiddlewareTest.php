<?php
namespace Tests\App\Auth;

use App\Auth\ForbiddenMiddleware;
use Framework\Auth\ForbiddenException;
use Framework\Auth\User;
use Framework\Session\ArraySession;
use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ForbiddenMiddlewareTest extends TestCase
{
    /**
     * @var \Framework\Session\SessionInterface
     */
    private $session;

    public function setUp()
    {
        $this->session = new ArraySession;
    }

    public function makeRequest($path = '/')
    {
        $uri = $this->getMockBuilder(UriInterface::class)->getMock();
        $uri->method('getPath')->willReturn($path);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getUri')->willReturn($uri);
        return $request;
    }

    public function makeRequestHandler()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        return $handler;
    }

    public function makeMiddleware()
    {
        return new ForbiddenMiddleware('/login', $this->session);
    }

    public function testCatchForbiddenException()
    {
        $handler = $this->makeRequestHandler();
        $handler->expects($this->once())->method('handle')->willThrowException(new ForbiddenException);
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testCatchTypeErrorException()
    {
        $handler = $this->makeRequestHandler();
        $handler->expects($this->once())->method('handle')->willReturnCallback(function (User $user) {
            return true;
        });
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testBubbleError()
    {
        $handler = $this->makeRequestHandler();
        $handler->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new \TypeError("test", 200);
        });
        $this->expectExceptionCode(200);
        $this->expectExceptionMessage("test");
        $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
    }

    public function testProcessValidRequest()
    {
        $handler = $this->makeRequestHandler();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        self::assertSame(
            $response,
            $this->makeMiddleware()->process($this->makeRequest('/test'), $handler)
        );
    }
}
