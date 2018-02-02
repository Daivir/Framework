<?php
namespace App\Shop\Table;

use App\Auth\User;
use App\Shop\Entity\Product;
use App\Shop\Entity\Purchase;
use Framework\Database\QueryResult;
use Framework\Database\Table;

class PurchaseTable extends Table
{
    protected $entity = Purchase::class;
    protected $table = 'purchases';

    /**
     * Find each products purchased by a user.
     * @param Product $product
     * @param User $user
     * @return null|object|Purchase
     */
    public function findEach(Product $product, User $user): ?Purchase
    {
        return $this->makeQuery()
            ->where('product_id = :product AND user_id = :user')
            ->params([
                'user' => $user->getId(),
                'product' => $product->getId()
            ])
            ->fetch() ?: null;
    }

    public function findForUser(User $user): QueryResult
    {
        return $this->makeQuery()
            ->select('p.*, pr.name as product_name')
            ->join('products as pr', 'pr.id = p.product_id')
            ->where('p.user_id = :user')
            ->params(['user' => $user->getId()])
            ->fetchAll();
    }

    /**
     * @param int $purchaseId
     * @return Purchase|null
     * @throws \Framework\Database\NoRecordException
     */
    public function findWithProduct(int $purchaseId): ?Purchase
    {
        return $this->makeQuery()
            ->select('p.*, pr.name as product_name')
            ->join('products as pr', 'pr.id = p.product_id')
            ->where("p.id = $purchaseId")
            ->fetchOrException();
    }

    public function getMonthRevenue(): ?string
    {
        return $this->makeQuery()
            ->select('SUM(price)')
            ->where('p.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()')
            ->fetchColumn();
    }
}
