<?php
namespace App\Account;

use Virton\Auth\LoggedInMiddleware;
use Virton\Module;
use Virton\Renderer\RendererInterface;
use Virton\Router;

class AccountModule extends Module
{
    const MIGRATIONS = __DIR__ . '/migrations';
    const DEFINITIONS = __DIR__ . '/definitions.php';

    public function __construct(Router $router, RendererInterface $renderer)
    {
        $renderer->addPath('account', __DIR__ . '/views');
        $router->get('/signup', Actions\SignupAction::class, 'account.signup');
        $router->post('/signup', Actions\SignupAction::class);
        $router->get('/account', [LoggedInMiddleware::class, Actions\AccountAction::class], 'account');
        $router->post('/account', [LoggedInMiddleware::class, Actions\AccountEditAction::class]);
    }
}
