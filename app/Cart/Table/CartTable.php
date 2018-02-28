<?php
namespace App\Cart\Table;

/*
 * TODO: Faire les tests
 */

use App\Cart\Cart;
use App\Cart\CartRow;
use App\Cart\Entity\Cart as CartEntity;
use App\Shop\Entity\Product;
use App\Shop\Table\ProductTable;
use Virton\Database\Hydrator;
use Virton\Database\Table;

class CartTable extends Table
{
    protected $table = 'carts';

    protected $entity = CartEntity::class;

    /**
     * @var ProductTable
     */
    private $productTable;

    /**
     * @var CartRowTable
     */
    private $cartRowTable;

    public function __construct(\PDO $pdo)
    {
        $this->productTable = new ProductTable($pdo);
        $this->cartRowTable = new CartRowTable($pdo);
        parent::__construct($pdo);
    }

    /**
     * @param Cart $cart
     */
    public function hydrateCart(Cart $cart): void
    {
        $rows = $cart->getRows();
        if (!empty($rows)) {
            $productIds = array_map(function (CartRow $row) {
                return $row->getProductId();
            }, $rows);
            /** @var Product[] $products */
            $products = $this->productTable->makeQuery()
                ->where('id IN (' . implode(',', $productIds) . ')')
                ->fetchAll();
            $productsById = [];
            foreach ($products as $product) {
                $productsById[$product->getId()] = $product;
            }
            foreach ($rows as $row) {
                $row->setProduct($productsById[$row->getProductId()]);
            }
        }
    }

    /**
     * @param int $userId
     * @return CartEntity
     */
    public function createForUser(int $userId): CartEntity
    {
        $params = [
            'user_id' => $userId
        ];
        $this->insert($params);
        $params['id'] = $this->pdo->lastInsertId();
        return Hydrator::hydrate($params, $this->entity);
    }

    public function findForUser(int $userId): ?CartEntity
    {
        return $this->makeQuery()
            ->where("user_id = $userId")
            ->fetch() ?: null;
    }

    public function findRows(CartEntity $cartEntity): array
    {
        return $this->cartRowTable->makeQuery()
            ->where("cart_id = {$cartEntity->getId()}")
            ->fetchAll()
            ->toArray();
    }

    /**
     * @param CartEntity $cart
     * @param Product $product
     * @param int $quantity
     * @return CartRow
     */
    public function addRow(CartEntity $cart, Product $product, int $quantity = 1): CartRow
    {
        $params = [
            'cart_id' => $cart->getId(),
            'product_id' => $product->getId(),
            'quantity' => $quantity
        ];
        $this->cartRowTable->insert($params);
        $params['id'] = $this->pdo->lastInsertId();
        /** @var CartRow $row */
        $row = Hydrator::hydrate($params, $this->cartRowTable->getEntity());
        $row->setProduct($product);
        return $row;
    }

    public function updateRowQuantity(CartRow $row, int $quantity): CartRow
    {
        $this->cartRowTable->update($row->getId(), ['quantity' => $quantity]);
        $row->setQuantity($quantity);
        return $row;
    }

    public function deleteRow(CartRow $row): void
    {
        $this->cartRowTable->delete($row->getId());
    }

    public function deleteRows(CartEntity $entity)
    {
        return $this->pdo->exec("DELETE FROM carts_products WHERE cart_id = {$entity->getId()}");
    }

    /**
     * @return ProductTable
     */
    public function getProductTable(): ProductTable
    {
        return $this->productTable;
    }
}
