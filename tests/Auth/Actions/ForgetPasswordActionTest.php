<?php
namespace Tests\Auth\Actions;

use App\Auth\Actions\ForgetPasswordAction;
use App\Auth\Mailer\ResetPasswordMailer;
use App\Auth\User;
use App\Auth\UserTable;
use Virton\Database\NoRecordException;
use Virton\Renderer\RendererInterface;
use Virton\Session\FlashHandler;
use Prophecy\Argument;
use Tests\ActionTestCase;

class ForgetPasswordActionTest extends ActionTestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $renderer;

    /**
     * @var ForgetPasswordAction
     */
    private $action;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $userTable;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $mailer;

    public function setUp()
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->userTable = $this->prophesize(UserTable::class);
        $this->mailer = $this->prophesize(ResetPasswordMailer::class);
        $this->action = new ForgetPasswordAction(
            $this->renderer->reveal(),
            $this->userTable->reveal(),
            $this->prophesize(FlashHandler::class)->reveal(),
            $this->mailer->reveal()
        );
    }

    public function testEmailInvalid()
    {
        $request = $this->makeRequest('/test', ['email' => 'email-invalid']);
        $this->renderer
            ->render(Argument::type('string'), Argument::withEntry('errors', Argument::withKey('email')))
            ->shouldBeCalled()
            ->willReturnArgument();
        $response = call_user_func($this->action, $request);
        $this->assertEquals('@auth/password', $response);
    }

    public function testEmailValid()
    {
        $token = "fake";
        $user = new User;
        $user->setId(3);
        $user->setEmail('john@doe.com');

        $request = $this->makeRequest('/test', ['email' => $user->email]);
        $this->userTable->findBy('email', $user->getEmail())->willReturn($user);
        $this->userTable->resetPassword($user->getId())->willReturn($token);
        $this->mailer->send($user->getEmail(), [
            'id' => $user->getId(),
            'token' => $token
        ])->shouldBeCalled();
        $this->renderer->render()->shouldNotBeCalled();
        $response = call_user_func($this->action, $request);
        $this->assertRedirect($response, '/test');
    }

    public function testEmailNotFound()
    {
        $request = $this->makeRequest('/test', ['email' => 'local@domain.dev']);
        $this->userTable->findBy('email', 'local@domain.dev')->willThrow(new NoRecordException);
        $this->renderer
            ->render(Argument::type('string'), Argument::withEntry('errors', Argument::withKey('email')))
            ->shouldBeCalled()
            ->willReturnArgument();
        $response = call_user_func($this->action, $request);
        $this->assertEquals('@auth/password', $response);
    }
}
