<?php
namespace App\Cart\Table;

use App\Cart\Entity\OrderRow;
use Framework\Database\Table;

class OrderRowTable extends Table
{
    protected $table = 'orders_products';

    protected $entity = OrderRow::class;
}
