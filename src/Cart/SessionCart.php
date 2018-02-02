<?php
namespace App\Cart;

use App\Shop\Entity\Product;
use Framework\Session\SessionInterface;

class SessionCart extends Cart
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $rows = $this->session->get('cart', []);
        $this->rows = array_map(function ($row) {
            $cartRow = new CartRow();
            $cartRow->setProductId($row['id']);
            $product = new Product();
            $product->setId($row['id']);
            $cartRow->setProduct($product);
            $cartRow->setQuantity($row['quantity']);
            return $cartRow;
        }, $rows);
    }

    public function addProduct(Product $product, ?int $quantity = null): void
    {
        parent::addProduct($product, $quantity);
        $this->persist();
    }

    public function removeProduct(Product $product): void
    {
        parent::removeProduct($product);
        $this->persist();
    }

    public function empty(): void
    {
        parent::empty();
        $this->persist();
    }

    private function persist(): void
    {
        $this->session->set('cart', $this->serialize());
    }

    private function serialize(): array
    {
        return array_map(function (CartRow $row) {
            return [
                'id' => $row->getProductId(),
                'quantity' => $row->getQuantity()
            ];
        }, $this->rows);
    }
}
