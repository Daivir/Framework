<?php
namespace App\Cart;

use App\Cart\Entity\Cart;
use App\Cart\Table\CartTable;
use App\Shop\Entity\Product;
use App\Cart\Cart as CartClass;

class DatabaseCart extends CartClass
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var null|Cart
     */
    private $cartEntity;

    /**
     * @var CartTable
     */
    private $cartTable;

    public function __construct(int $userId, CartTable $cartTable)
    {
        $this->userId = $userId;
        $this->cartTable = $cartTable;
        $this->cartEntity = $this->cartTable->findForUser($userId);
        if ($this->cartEntity) {
            $this->rows = $this->cartTable->findRows($this->cartEntity);
        }
    }

    public function addProduct(Product $product, ?int $quantity = null): void
    {
        if ($this->cartEntity === null) {
            $this->cartEntity = $this->cartTable->createForUser($this->userId);
        }
        if ($quantity === 0) {
            $this->removeProduct($product);
        } else {
            $row = $this->getRow($product);
            if ($row === null) {
                $this->rows[] = $this->cartTable->addRow($this->cartEntity, $product, $quantity ?: 1);
            } else {
                $this->cartTable->updateRowQuantity($row, $quantity ?: ($row->getQuantity() + 1));
            }
        }
    }

    public function removeProduct(Product $product): void
    {
        $row = $this->getRow($product);
        $this->cartTable->deleteRow($row);
        parent::removeProduct($product);
    }

    public function merge(CartClass $cart)
    {
        $rows = $cart->getRows();
        foreach ($rows as $r) {
            $row = $this->getRow($r->getProduct());
            if ($row) {
                $this->addProduct($r->getProduct(), $row->getQuantity() + $r->getQuantity());
            } else {
                $this->addProduct($r->getProduct(), $r->getQuantity());
            }
        }
    }

    public function empty(): void
    {
        $this->cartTable->deleteRows($this->cartEntity);
        parent::empty();
    }
}
