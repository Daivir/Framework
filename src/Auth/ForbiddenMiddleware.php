<?php
namespace App\Auth;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Virton\Auth\ForbiddenException;
use Virton\Response\RedirectResponse;
use Virton\Session\SessionInterface;
use Virton\Session\FlashHandler;

class ForbiddenMiddleware implements MiddlewareInterface
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
     * @throws \TypeError
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            return $this->redirectLogin($request);
        } catch (\TypeError $e) {
            if (strpos($e->getMessage(), \Virton\Auth\User::class) !== false) {
                return $this->redirectLogin($request);
            }
            throw $e;
        }
    }

    private function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashHandler($this->session))->warning('Access denied: You cannot access this page.');
        return new RedirectResponse($this->loginPath);
    }
}
