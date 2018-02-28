<?php
namespace App\Auth\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Virton\Renderer\RendererInterface;
use App\Auth\DatabaseAuth;
use Virton\Response\RedirectResponse;
use Virton\Session\FlashHandler;


class LogoutAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var DatabaseAuth
     */
    private $auth;

    /**
     * @var FlashHandler
     */
    private $flash;

    public function __construct(RendererInterface $renderer, DatabaseAuth $auth, FlashHandler $flash)
    {
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->flash = $flash;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $this->auth->logout();
        $this->flash->success('You are now disconnected');
        return new RedirectResponse('/blog');
    }
}
