<?php
namespace App\Shop\Action;

/*
 * TODO: utiliser wkhtmltopdf
 */

use App\Shop\Table\PurchaseTable;
use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class InvoiceAction
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
        $purchase = $this->purchaseTable->findWithProduct($request->getAttribute('id'));
        /** @var \App\Auth\User $user */
        $user = $this->auth->getUser();
        if ($user->getId() !== $purchase->getUserId()) {
            throw new ForbiddenException('You cannot download this invoice.');
        }
        return $this->renderer->render('@shop/invoice', compact('purchase', 'user'));
    }
}
