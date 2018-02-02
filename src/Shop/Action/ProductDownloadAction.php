<?php
namespace App\Shop\Action;

use App\Shop\Table\ProductTable;
use App\Shop\Table\PurchaseTable;
use Framework\Auth;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

class ProductDownloadAction
{
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

    public function __construct(ProductTable $productTable, PurchaseTable $purchaseTable, Auth $auth)
    {
        $this->productTable = $productTable;
        $this->purchaseTable = $purchaseTable;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        /** @var \App\Shop\Entity\Product $product */
        $product = $this->productTable->find($request->getAttribute('id'));
        $user = $this->auth->getUser();
        if ($this->purchaseTable->findEach($product, $user)) {
            $source = fopen("resources/downloads/{$product->getPdf()}", 'r');
            return new Response(200, ['Content-Type' => 'application/pdf'], $source);
        } else {
            throw new Auth\ForbiddenException;
        }
    }
}
