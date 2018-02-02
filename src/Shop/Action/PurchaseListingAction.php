<?php
namespace App\Shop\Action;

use App\Shop\Table\PurchaseTable;
use Framework\Auth;
use Framework\Database\Hydrator;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PurchaseListingAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var PurchaseTable
     */
    private $purchaseTable;
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(RendererInterface $renderer, PurchaseTable $purchaseTable, Auth $auth)
    {
        $this->renderer = $renderer;
        $this->purchaseTable = $purchaseTable;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();
        $purchases = $this->purchaseTable->findForUser($user);
        return $this->renderer->render('@shop/purchases', compact('purchases'));
    }
}
