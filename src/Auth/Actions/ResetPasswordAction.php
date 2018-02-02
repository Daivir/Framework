<?php
namespace App\Auth\Actions;

use App\Auth\UserTable;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router;
use Framework\Session\FlashHandler;
use Framework\Validator;
use Psr\Http\Message\ServerRequestInterface;

class ResetPasswordAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var UserTable
     */
    private $userTable;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var FlashHandler
     */
    private $flash;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        FlashHandler $flash,
        Router $router
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->router = $router;
        $this->flash = $flash;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        /** @var \App\Auth\User $user */
        $user = $this->userTable->find($request->getAttribute('id'));
        if ($user->getPasswordReset() !== null &&
            $user->getPasswordReset() === $request->getAttribute('token') &&
            time() - $user->getPasswordResetAt()->getTimestamp() < (10 * 60)
        ) {
            if ($request->getMethod() === 'GET') {
                return $this->renderer->render('@auth/reset');
            }
            $params = $request->getParsedBody();
            $validator = (new Validator($params))
                ->confirm('password')
                ->length('password', 4)
                ->notEmpty('password_confirm')
                ->required('password', 'password_confirm');
            if ($validator->isValid()) {
                $this->userTable->updatePassword($user->getId(), $params['password']);
                $this->flash->success('Your password has been successufully updated');
                return new RedirectResponse($this->router->generateUri('auth.login'));
            }
            $errors = $validator->getErrors();
            return $this->renderer->render('@auth/reset', compact('errors'));
        } else {
            $this->flash->danger('Invalid token (Expired)');
            return new RedirectResponse($this->router->generateUri('auth.password'));
        }
    }
}
