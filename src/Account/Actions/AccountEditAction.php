<?php
namespace App\Account\Actions;

use App\Auth\UserTable;
use Virton\Auth;
use Virton\Renderer\RendererInterface;
use Virton\Response\RedirectResponse;
use Virton\Session\FlashHandler;
use Virton\Validator;
use Psr\Http\Message\ServerRequestInterface;

class AccountEditAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var Auth
     */
    private $auth;
    /**
     * @var FlashHandler
     */
    private $flash;
    /**
     * @var UserTable
     */
    private $userTable;

    /**
     * AccountEditAction constructor.
     * @param RendererInterface $renderer
     * @param Auth $auth
     * @param FlashHandler $flash
     * @param UserTable $userTable
     */
    public function __construct(RendererInterface $renderer, Auth $auth, FlashHandler $flash, UserTable $userTable)
    {
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->flash = $flash;
        $this->userTable = $userTable;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->confirm('password')
            ->required('firstname', 'lastname');
        if ($validator->isValid()) {
            $userParams = [
                'firstname' => $params['firstname'],
                'lastname'  => $params['lastname']
            ];
            if (!empty($params['password'])) {
                $userParams['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
            }
            $this->userTable->update($user->id, $userParams);
            $this->flash->success('Your account have been successfully updated');
            return new RedirectResponse($request->getUri()->getPath());
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@account/account', compact('user', 'errors'));
    }
}
