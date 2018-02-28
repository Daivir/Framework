<?php
namespace App\Cart\Table;

use App\Cart\CartRow;
use Virton\Database\Table;

class CartRowTable extends Table
{
    protected $table = 'carts_products';
    protected $entity = CartRow::class;
}
