<?php
namespace App\Cart;

use App\Shop\Entity\Product;

class Cart
{
    /**
     * @var CartRow[]
     */
    protected $rows = [];

    /**
     * Adds products to the cart.
     * @param Product $product
     * @param int|null $quantity
     */
    public function addProduct(Product $product, ?int $quantity = null): void
    {
        if ($quantity === 0) {
            $this->removeProduct($product);
        } else {
            $row = $this->getRow($product);
            if ($row === null) {
                $row = new CartRow();
                $row->setProduct($product);
                $this->rows[] = $row;
            } else {
                $row->setQuantity($row->getQuantity() + 1);
            }
            if (!is_null($quantity)) {
                $row->setQuantity($quantity);
            }
        }
    }

    /**
     * Deletes products to the cart.
     * @param Product $product
     */
    public function removeProduct(Product $product): void
    {
        $this->rows = array_filter($this->rows, function (CartRow $row) use ($product) {
            return $row->getProductId() !== $product->getId();
        });
    }

    /**
     * Number of products in the cart.
     * @return int
     */
    public function count(): int
    {
        return array_reduce($this->rows, function (int $i, CartRow $row) {
            return $row->getQuantity() + $i;
        }, 0);
    }

    /**
     * @return CartRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param Product $product
     * @return CartRow|null
     */
    protected function getRow(Product $product): ?CartRow
    {
        /** @var CartRow $row */
        foreach ($this->rows as $row) {
            if ($row->getProductId() === $product->getId()) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Retreives the total price of the cart.
     * @return float
     */
    public function getPrice(): float
    {
        return array_reduce($this->rows, function (int $i, CartRow $row) {
            return $row->getQuantity() * $row->getProduct()->getPrice() + $i;
        }, 0);
    }

    /**
     * Empty the cart.
     */
    public function empty(): void
    {
        $this->rows = [];
    }
}
