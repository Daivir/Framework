<?php
namespace App\Contact;

use Virton\Renderer\RendererInterface;
use Virton\Response\RedirectResponse;
use Virton\Session\FlashHandler;
use Virton\Validator;
use Psr\Http\Message\ServerRequestInterface;

class ContactAction
{
    /**
     * @var string
     */
    private $to;
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var FlashHandler
     */
    private $flash;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(
        string $to,
        RendererInterface $renderer,
        FlashHandler $flash,
        \Swift_Mailer $mailer
    ) {
        $this->to = $to;
        $this->renderer = $renderer;
        $this->flash = $flash;
        $this->mailer = $mailer;
    }

    /**
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     */
    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@contact/contact');
        }
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->required('name', 'email', 'content')
            ->length('name', 4)
            ->email('email')
            ->length('content', 15);
        if ($validator->isValid()) {
            $this->flash->success('Thanks for the message!');
            // TODO: Create Mailer class, working like: $mailer->send('Title', 'path/to/view', $params)
            $message = new \Swift_Message('Contact form');
            $message->setBody($this->renderer->render('@contact/email/contact.text', $params));
	        $message->addPart($this->renderer->render('@contact/email/contact.html', $params), 'text/html');
            $message->setTo($this->to);
            $message->setFrom($params['email']);
            $this->mailer->send($message);
            return new RedirectResponse((string)$request->getUri());
        } else {
            $this->flash->danger('Please, correct your mistakes.');
            $errors = $validator->getErrors();
            return $this->renderer->render('@contact/contact', compact('errors'));
        }
    }
}
