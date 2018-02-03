<?php
namespace Tests\App\Contact;

use App\Contact\ContactAction;
use Virton\Renderer\RendererInterface;
use Virton\Response\RedirectResponse;
use Virton\Session\FlashHandler;
use Tests\ActionTestCase;

class ContactActionTest extends ActionTestCase
{
    /**
     * @var string
     */
    private $to = 'contact@local.dev';
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var ContactAction
     */
    private $action;
    /**
     * @var FlashHandler
     */
    private $flash;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function setUp()
    {
        $this->renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
        $this->flash = $this->getMockBuilder(FlashHandler::class)->disableOriginalConstructor()->getMock();
        $this->mailer = $this->getMockBuilder(\Swift_Mailer::class)->disableOriginalConstructor()->getMock();
        $this->action = new ContactAction($this->to, $this->renderer, $this->flash, $this->mailer);
    }

    public function testGet()
    {
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@contact/contact')
            ->willReturn('');
        call_user_func($this->action, $this->makeRequest('/contact'));
    }

    public function testPostInvalid()
    {
        $request = $this->makeRequest('/contact', [
            'name' => 'Marc',
            'email' => 'invalid-email',
            'content' => 'Lorem ipsum dolor sit amet'
        ]);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                '@contact/contact',
                $this->callback(function ($params) {
                    $this->assertArrayHasKey('errors', $params);
                    $this->assertArrayHasKey('email', $params['errors']);
                    return true;
                })
            )
            ->willReturn('');
        $this->flash->expects($this->once())->method('danger');
        call_user_func($this->action, $request);
    }

    public function testPostValid()
    {
        $request = $this->makeRequest('/contact', [
            'name' => 'Marc D.',
            'email' => 'test@local.dev',
            'content' => 'Lorem ipsum dolor sit amet'
        ]);
        $this->flash->expects($this->once())->method('success');
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (\Swift_Message $message) {
                $this->assertArrayHasKey($this->to, $message->getTo());
                $this->assertArrayHasKey('test@local.dev', $message->getFrom());
                $this->assertContains('text', $message->toString());
                $this->assertContains('html', $message->toString());
                return true;
            }));
        $this->renderer->expects($this->any())
            ->method('render')
            ->willReturn('text', 'html');
        $response = call_user_func($this->action, $request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
