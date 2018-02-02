<?php
namespace App\Cart\Action;

use App\Cart\Table\OrderTable;
use Framework\Auth;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class OrderListingAction
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
        $currentPage = $request->getQueryParams()['p'] ?? 1;
        $orders = $this->orderTable->findForUser($this->auth->getUser())->paginate(10, $currentPage);
        $this->orderTable->findRows($orders);
        return $this->renderer->render('@cart/orders', compact('orders', 'currentPage'));
    }
}
