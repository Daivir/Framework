<?php
namespace App\Shop\Table;

use App\Shop\Entity\Product;
use Virton\Database\Query;
use Virton\Database\Table;

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
