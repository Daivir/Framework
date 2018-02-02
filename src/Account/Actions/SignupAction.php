<?php

namespace App\Account\Actions;

use App\Auth\DatabaseAuth;
use App\Auth\User;
use App\Auth\UserTable;
use Framework\Database\Hydrator;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router;
use Framework\Session\FlashHandler;
use Framework\Validator;
use Psr\Http\Message\ServerRequestInterface;

class SignupAction
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
     * @var DatabaseAuth
     */
    private $auth;
    /**
     * @var FlashHandler
     */
    private $flash;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        Router $router,
        DatabaseAuth $auth,
        FlashHandler $flash
    ) {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->router = $router;
        $this->auth = $auth;
        $this->flash = $flash;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@account/signup');
        }
        $validator = (new Validator($params))
            ->length('username', 5)
            ->email('email')
            ->confirm('password')
            ->unique('username', $this->userTable)
            ->unique('email', $this->userTable)
            ->required('username', 'email', 'password', 'password_confirm');
        if ($validator->isValid()) {
            $userParams = [
                'username' => $params['username'],
                'email' => $params['email'],
                'password' => password_hash($params['password'], PASSWORD_DEFAULT)
            ];
            $this->userTable->insert($userParams);
            $user = Hydrator::hydrate($userParams, User::class);
            $user->setId($this->userTable->getPdo()->lastInsertId());
            $this->auth->setUser($user);
            $this->flash->success('Your account has been successfully created!');
            return new RedirectResponse($this->router->generateUri('account'));
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@account/signup', [
            'errors' => $errors,
            'user' => [
                'username'  => $params['username'],
                'email'     => $params['email']
            ]
        ]);
    }
}
