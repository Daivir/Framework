<?php
namespace App\Shop\Table;

use App\Shop\Entity\Product;
use Framework\Database\Query;
use Framework\Database\Table;

class ProductTable extends Table
{
    /**
     * @inheritDoc
     */
    protected $entity = Product::class;

    /**
     * @inheritDoc
     */
    protected $table = "products";

    public function findPublic(): Query
    {
        return $this->makeQuery()->where('created_at < NOW()');
    }
}
