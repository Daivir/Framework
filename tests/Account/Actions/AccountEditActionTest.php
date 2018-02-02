<?php
namespace Tests\Account\Actions;

use App\Account\Actions\AccountEditAction;
use App\Account\User;
use App\Auth\UserTable;
use Framework\Auth;
use Framework\Renderer\RendererInterface;
use Framework\Session\FlashHandler;
use Prophecy\Argument;
use Tests\ActionTestCase;

class AccountEditActionTest extends ActionTestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $renderer;
    private $auth;
    private $userTable;

    /**
     * @var AccountEditAction
     */
    private $action;

    /**
     * @var User
     */
    private $user;

    protected function setUp()
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->auth = $this->prophesize(Auth::class);
        $this->user = new User();
        $this->user->id = 3;
        $this->auth->getUser()->willReturn($this->user);
        $this->userTable = $this->prophesize(UserTable::class);
        $this->action = new AccountEditAction(
            $this->renderer->reveal(),
            $this->auth->reveal(),
            $this->prophesize(FlashHandler::class)->reveal(),
            $this->userTable->reveal()
        );
    }

    public function testValid()
    {
        $this->userTable->update(3, [
            'firstname' => 'John',
            'lastname' => 'Doe'
        ])->shouldBeCalled();
        $response = call_user_func($this->action, $this->makeRequest('/test', [
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]));
        $this->assertRedirect($response, '/test');
    }

    public function testValidWithPassword()
    {
        $this->userTable->update(3, Argument::that(function ($params) {
            $this->assertEquals(['firstname', 'lastname', 'password'], array_keys($params));
            return true;
        }))->shouldBeCalled();
        $response = call_user_func($this->action, $this->makeRequest('/test', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'password' => 'test',
            'password_confirm' => 'test'
        ]));
        $this->assertRedirect($response, '/test');
    }

    public function testPostInvalid()
    {
        $this->userTable->update()->shouldNotBeCalled();
        $this->renderer->render('@account/account', Argument::that(function ($params) {
            $this->assertEquals(['password'], array_keys($params['errors']));
            return true;
        }));
    }
}
