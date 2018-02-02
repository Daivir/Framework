<?php
namespace App\Auth;

use App\Auth\Actions\ResetPasswordAction;
use Framework\Module;
use Psr\Container\ContainerInterface;
use App\Auth\Actions\LoginAction;
use App\Auth\Actions\LoginAttemptAction;
use App\Auth\Actions\ForgetPasswordAction;
use Framework\Router;
use Framework\Renderer\RendererInterface;
use App\Auth\Actions\LogoutAction;

class AuthModule extends Module
{
    const DEFINITIONS = __DIR__ . '/config.php';
    const MIGRATIONS = __DIR__ . '/db/migrations';
    const SEEDS = __DIR__ . '/db/seeds';

    public function __construct(ContainerInterface $container, Router $router, RendererInterface $renderer)
    {
        $renderer->addPath('auth', __DIR__ . '/views');
        $router->get($container->get('auth.login'), LoginAction::class, 'auth.login');
        $router->post($container->get('auth.login'), LoginAttemptAction::class);
        $router->post($container->get('auth.logout'), LogoutAction::class, 'auth.logout');
        $router->any($container->get('auth.password'), ForgetPasswordAction::class, 'auth.password');
        $router->any($container->get('auth.reset'), ResetPasswordAction::class, 'auth.reset');
    }
}
