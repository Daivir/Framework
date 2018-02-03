<?php
namespace App\Cart\Table;

use App\Cart\Entity\OrderRow;
use Virton\Database\Table;

class OrderRowTable extends Table
{
    protected $table = 'orders_products';

    protected $entity = OrderRow::class;
}
