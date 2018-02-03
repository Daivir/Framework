<?php
namespace Tests\App\Account\Actions;

use App\Account\Actions\SignupAction;
use App\Auth\DatabaseAuth;
use App\Auth\User;
use App\Auth\UserTable;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use Virton\Session\FlashHandler;
use Prophecy\Argument;
use Tests\ActionTestCase;

class SignupActionTest extends ActionTestCase
{
    /**
     * @var SignupAction
     */
    private $action;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $renderer;
    private $userTable;
    private $router;
    private $auth;
    private $flash;

    public function setUp()
    {
        $this->userTable = $this->prophesize(UserTable::class);
        $this->statement();
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->renderer->render(Argument::any(), Argument::any())->willReturn('');
        $this->router = $this->prophesize(Router::class);
        $this->router->generateUri(Argument::any())->will(function ($args) {
            return $args[0]; // route name
        });
        $this->auth = $this->prophesize(DatabaseAuth::class);
        $this->flash = $this->prophesize(FlashHandler::class);
        $this->action = new SignupAction(
            $this->renderer->reveal(),
            $this->userTable->reveal(),
            $this->router->reveal(),
            $this->auth->reveal(),
            $this->flash->reveal()
        );
    }

    public function statement()
    {
        $pdo = $this->prophesize(\PDO::class);
        $statement = $this->getMockBuilder(\PDOStatement::class)->getMock();
        $statement->expects($this->any())->method('fetchColumn')->willReturn(false);
        $pdo->prepare(Argument::any())->willReturn($statement);
        $pdo->lastInsertId()->willReturn(3);
        $this->userTable->getTable()->willReturn('fake');
        $this->userTable->getPdo()->willReturn($pdo->reveal());
    }

    public function testGet()
    {
        call_user_func($this->action, $this->makeRequest());
        $this->renderer->render('@account/signup')->shouldHaveBeenCalled();
    }

    public function testPostInvalid()
    {
        call_user_func($this->action, $this->makeRequest('/test', [
            'username' => 'John Doe',
            'email' => 'email-invalid',
            'password' => 'user-password',
            'password_confirm' => 'invalid-password'
        ]));
        $this->renderer->render('@account/signup', Argument::that(function ($params) {
            $this->assertArrayHasKey('errors', $params);
            $this->assertEquals(['email', 'password'], array_keys($params['errors']));
            return true;
        }))->shouldHaveBeenCalled();
    }

    public function testPostValid()
    {
        $this->userTable->insert(Argument::that(function ($userParams) {
            $this->assertArraySubset([
                'username' => 'John Doe',
                'email' => 'john@doe.dev'
            ], $userParams);
            $this->assertTrue(password_verify('test', $userParams['password']));
            return true;
        }))->shouldBeCalled();
        $this->auth->setUser(Argument::that(function (User $user) {
            $this->assertEquals('John Doe', $user->username);
            $this->assertEquals('john@doe.dev', $user->email);
            $this->assertEquals(3, $user->id);
            return true;
        }))->shouldBeCalled();
        $this->flash->success(Argument::type('string'))->shouldBeCalled();
        $response = call_user_func($this->action, $this->makeRequest('/test', [
            'username' => 'John Doe',
            'email' => 'john@doe.dev',
            'password' => 'test',
            'password_confirm' => 'test'
        ]));
        $this->renderer->render()->shouldNotHaveBeenCalled();
        $this->assertRedirect($response, 'account');
    }

    public function testPostWithNoPassword()
    {
        call_user_func($this->action, $this->makeRequest('/test', [
            'username' => 'John Doe',
            'email' => 'email-invalid',
            'password' => '',
            'password_confirm' => ''
        ]));
        $this->renderer->render('@account/signup', Argument::that(function ($params) {
            $this->assertArrayHasKey('errors', $params);
            $this->assertEquals(['email', 'password', 'password_confirm'], array_keys($params['errors']));
            return true;
        }))->shouldHaveBeenCalled();
    }
}
