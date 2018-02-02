<?php
namespace App\Shop\Action;

use App\Shop\Table\ProductTable;
use App\Shop\Table\PurchaseTable;
use Framework\Auth;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductShowAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var ProductTable
     */
    private $productTable;
    /**
     * @var PurchaseTable
     */
    private $purchaseTable;
    /**
     * @var Auth
     */
    private $auth;
    /**
     * @var string
     */
    private $stripeKey;


    public function __construct(
        RendererInterface $renderer,
        ProductTable $productTable,
        PurchaseTable $purchaseTable,
        Auth $auth,
        string $stripeKey
    ) {
        $this->renderer = $renderer;
        $this->productTable = $productTable;
        $this->purchaseTable = $purchaseTable;
        $this->auth = $auth;
        $this->stripeKey = $stripeKey;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        // TODO: Redirect current page when logged in
        $product = $this->productTable->findBy('slug', $request->getAttribute('slug'));
        $stripeKey = $this->stripeKey;
        $canDownload = false;
        $user = $this->auth->getUser();
        if (!is_null($user) && $this->purchaseTable->findEach($product, $user)) {
            $canDownload = true;
        }
        return $this->renderer->render('@shop/show', compact('product', 'stripeKey', 'canDownload'));
    }
}
