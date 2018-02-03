<?php
namespace Tests\Auth;

use Virton\Auth;
use Virton\Auth\RoleMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class RoleMiddlewareTest extends TestCase
{
	/**
	 * @var RoleMiddleware
	 */
	private $middleware;

	/**
	 * @var ObjectProphecy
	 */
	private $auth;

	public function setUp()
	{
		$this->auth = $this->prophesize(Auth::class);
		$this->middleware = new RoleMiddleware(
			$this->auth->reveal(),
			'admin'
		);
	}

	public function testUnauthenticatedUser()
	{
		$this->auth->getUser()->willReturn(null);
		$this->expectException(Auth\ForbiddenException::class);
		$this->middleware->process(new ServerRequest('GET', '/test'), $this->makeHandler()->reveal());
	}

	public function testInvalidRole()
	{
		$user = $this->prophesize(Auth\User::class);
		$user->getRoles()->willReturn(['user']);
		$this->auth->getUser()->willReturn($user->reveal());
		$this->expectException(Auth\ForbiddenException::class);
		$this->middleware->process(new ServerRequest('GET', '/test'), $this->makeHandler()->reveal());
	}

	public function testValidRole()
	{
		$user = $this->prophesize(Auth\User::class);
		$user->getRoles()->willReturn(['admin']);
		$this->auth->getUser()->willReturn($user->reveal());
		$handler = $this->makeHandler();
		$handler
			->handle(Argument::any())
			->shouldBeCalled()
			->willReturn(new Response());
		$this->middleware->process(new ServerRequest('GET', '/test'), $handler->reveal());
	}

	private function makeHandler(): ObjectProphecy
	{
		$handler = $this->prophesize(RequestHandlerInterface::class);
		$handler->handle(Argument::any())->willReturn(new Response());
		return $handler;
	}
}
