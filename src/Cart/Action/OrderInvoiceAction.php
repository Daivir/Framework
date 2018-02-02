<?php
namespace App\Cart\Action;

use App\Cart\Entity\Order;
use App\Cart\Table\OrderTable;
use Framework\Auth;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class OrderInvoiceAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var OrderTable
     */
    private $orderTable;
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(
        RendererInterface $renderer,
        OrderTable $orderTable,
        Auth $auth
    ) {
        $this->renderer = $renderer;
        $this->orderTable = $orderTable;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        /** @var Order $order */
        $order = $this->orderTable->find($request->getAttribute('id'));
        $this->orderTable->findRows([$order]);
        $user = $this->auth->getUser();
        if ($user->getId() !== $order->getUserId()) {
            throw new Auth\ForbiddenException('You cannot download this invoice');
        }
        return $this->renderer->render('@cart/invoice', compact('order', 'user'));
    }
}
