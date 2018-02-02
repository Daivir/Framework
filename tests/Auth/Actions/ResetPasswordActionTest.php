<?php
namespace Tests\Auth\Actions;

use App\Auth\Actions\ForgetPasswordAction;
use App\Auth\Actions\ResetPasswordAction;
use App\Auth\User;
use App\Auth\UserTable;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashHandler;
use Prophecy\Argument;
use Tests\ActionTestCase;

class ResetPasswordActionTest extends ActionTestCase
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

    public function setUp()
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->userTable = $this->prophesize(UserTable::class);
        $router = $this->prophesize(Router::class);
        $this->renderer->render(Argument::cetera())->willReturnArgument();
        $router->generateUri(Argument::cetera())->willReturnArgument();
        $this->action = new ResetPasswordAction(
            $this->renderer->reveal(),
            $this->userTable->reveal(),
            $this->prophesize(FlashHandler::class)->reveal(),
            $router->reveal()
        );
    }

    private function makeUser()
    {
        $user = new User();
        $user->setId(3);
        $user->setPasswordReset("fake");
        $user->setPasswordResetAt(new \DateTime);
        return $user;
    }

    public function testWithBadToken()
    {
        $user = $this->makeUser();
        $request = $this->makeRequest('/test')
            ->withAttribute('id', $user->getId())
            ->withAttribute('token', $user->getPasswordReset() . '-invalid');
        $this->userTable->find($user->getId())->willReturn($user);
        $response = call_user_func($this->action, $request);
        $this->assertRedirect($response, 'auth.password');
    }

    public function testWithExpiredToken()
    {
        $user = $this->makeUser();
        $user->setPasswordResetAt((new \DateTime)->sub(new \DateInterval('PT15M')));
        $request = $this->makeRequest('/test')
            ->withAttribute('id', $user->getId())
            ->withAttribute('token', $user->getPasswordReset());
        $this->userTable->find($user->getId())->willReturn($user);
        $response = call_user_func($this->action, $request);
        $this->assertRedirect($response, 'auth.password');
    }

    public function testWithValidToken()
    {
        $user = $this->makeUser();
        $user->setPasswordResetAt((new \DateTime)->sub(new \DateInterval('PT5M')));
        $request = $this->makeRequest('/test')
            ->withAttribute('id', $user->getId())
            ->withAttribute('token', $user->getPasswordReset());
        $this->userTable->find($user->getId())->willReturn($user);
        $response = call_user_func($this->action, $request);
        $this->assertEquals($response, '@auth/reset');
    }

    public function testPostWithBadPassword()
    {
        $user = $this->makeUser();
        $user->setPasswordResetAt((new \DateTime)->sub(new \DateInterval('PT5M')));
        $request = $this->makeRequest('/test', [
            'password' => 'password',
            'password_confirm' => 'password-invalid'
        ])
            ->withAttribute('id', $user->getId())
            ->withAttribute('token', $user->getPasswordReset());
        $this->userTable->find($user->getId())->willReturn($user);
        $this->renderer->render(Argument::type('string'), Argument::withKey('errors'))
            ->shouldBeCalled()
            ->willReturnArgument();
        $response = call_user_func($this->action, $request);
        $this->assertEquals($response, '@auth/reset');
    }

    public function testPostWithGoodPassword()
    {
        $user = $this->makeUser();
        $user->setPasswordResetAt((new \DateTime)->sub(new \DateInterval('PT5M')));
        $request = $this->makeRequest('/test', [
            'password' => 'password-valid',
            'password_confirm' => 'password-valid'
        ])
            ->withAttribute('id', $user->getId())
            ->withAttribute('token', $user->getPasswordReset());
        $this->userTable->find($user->getId())->willReturn($user);
        $this->userTable->updatePassword($user->getId(), 'password-valid')->shouldBeCalled();
        $response = call_user_func($this->action, $request);
        $this->assertRedirect($response, 'auth.login');
    }
}
