<?php
namespace App\Auth;

use Framework\Database\NoRecordException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Framework\Response\RedirectResponse;
use Framework\Session\SessionInterface;
use Framework\Session\FlashHandler;

class NoRecordMiddleware implements MiddlewareInterface
{
	/**
	 * @var string
	 */
	private $loginPath;

	/**
	 * @var SessionInterface
	 */
	private $session;

	public function __construct(string $loginPath, SessionInterface $session)
	{
		$this->loginPath = $loginPath;
		$this->session = $session;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try {
			return $handler->handle($request);
		} catch (NoRecordException $e) {
			return $this->redirectLogin($request);
		}
	}

	private function redirectLogin(ServerRequestInterface $request): ResponseInterface
	{
		$this->session->set('auth.redirect', $request->getUri()->getPath());
		(new FlashHandler($this->session))->danger('This account does not exists');
		return new RedirectResponse($this->loginPath);
	}
}
