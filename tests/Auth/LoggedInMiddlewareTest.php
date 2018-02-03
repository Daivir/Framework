<?php

namespace Tests\Auth;

use App\Auth\User;
use Virton\Auth;
use Virton\Auth\LoggedInMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Virton\Auth\ForbiddenException;

class LoggedInMiddlewareTest extends TestCase
{
	public function makeMiddleware(?User $user)
	{
		$auth = $this->getMockBuilder(Auth::class)->getMock();
		$auth->method('getUser')->willReturn($user);
		return new LoggedInMiddleware($auth);
	}

	public function makeHandler($calls)
	{
		$handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
		$response = $this->getMockBuilder(ResponseInterface::class)->getMock();
		$handler->expects($calls)->method('handle')->willReturn($response);
		return $handler;
	}

	public function testThrowIfNoUser()
	{
		$request = (new ServerRequest('GET', '/test/'));
		$this->expectException(ForbiddenException::class);
		$this->makeMiddleware(null)->process(
			$request,
			$this->makeHandler($this->never())
		);
	}

	public function testNextIfUser()
	{
		$user = $this->getMockBuilder(User::class)->getMock();
		$request = (new ServerRequest('GET', '/test/'));
		$this->makeMiddleware($user)->process(
			$request,
			$this->makeHandler($this->once())
		);
	}
}
