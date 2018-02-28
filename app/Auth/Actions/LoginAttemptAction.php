<?php
namespace App\Auth\Actions;

use App\Auth\Event\LoginEvent;
use Virton\EventManager\EventManager;
use Psr\Http\Message\ServerRequestInterface;
use Virton\Renderer\RendererInterface;
use App\Auth\DatabaseAuth;
use Virton\Router;
use Virton\Session\SessionInterface;
use Virton\Session\FlashHandler;
use Virton\Actions\RouterAwareAction;
use Virton\Response\RedirectResponse;

class LoginAttemptAction
{
    private $renderer;
    private $auth;
    private $router;
    private $session;
    /**
     * @var EventManager
     */
    private $eventManager;

    use RouterAwareAction;

    public function __construct(
        RendererInterface $renderer,
        DatabaseAuth $auth,
        Router $router,
        SessionInterface $session,
        EventManager $eventManager
    ) {
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->router = $router;
        $this->session = $session;
        $this->eventManager = $eventManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return RedirectResponse|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $user = $this->auth->login($params['username'], $params['password']) ?: null;
        if ($user) {
            $this->eventManager->trigger(new LoginEvent($user));
            $path = $this->session->get('auth.redirect') ?: $this->router->generateUri('account');
            $this->session->delete('auth.redirect');
            return new RedirectResponse($path);
        } else {
            (new FlashHandler($this->session))->danger('Username or password incorrect');
            return $this->redirect('auth.login');
        }
    }
}
