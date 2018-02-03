<?php
namespace App\Auth\Actions;

use App\Auth\Mailer\ResetPasswordMailer;
use App\Auth\UserTable;
use Virton\Database\NoRecordException;
use Virton\Renderer\RendererInterface;
use Virton\Response\RedirectResponse;
use Virton\Session\FlashHandler;
use Virton\Validator;
use Psr\Http\Message\ServerRequestInterface;

class ForgetPasswordAction
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
     * @var FlashHandler
     */
    private $flash;
    /**
     * @var ResetPasswordMailer
     */
    private $mailer;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        FlashHandler $flash,
        ResetPasswordMailer $mailer
    ) {

        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->flash = $flash;
        $this->mailer = $mailer;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@auth/password');
        }
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->email('email')
            ->notEmpty('email');
        if ($validator->isValid()) {
            try {
                $user = $this->userTable->findBy('email', $params['email']);
                $token = $this->userTable->resetPassword($user->id);
                $this->mailer->send($user->email, [
                    'id' => $user->id,
                    'token' => $token
                ]);
                $this->flash->success('An email has been sent to this email in order to reset your password');
                return new RedirectResponse($request->getUri()->getPath());
            } catch (NoRecordException $e) {
                $errors = ['email' => 'No user found with this email'];
            }
        } else {
            $errors = $validator->getErrors();
        }
        return $this->renderer->render('@auth/password', compact('errors'));
    }
}
