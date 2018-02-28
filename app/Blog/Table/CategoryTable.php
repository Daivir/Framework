<?php
namespace App\Blog\Table;

use App\Blog\Entity\Category;
use Virton\Database\Query;
use Virton\Database\QueryResult;
use Virton\Database\Table;

/**
 * Class CategoryTable.
 *
 * @package App\Blog\Table
 */
class CategoryTable extends Table
{
    protected $table = 'categories';
    protected $entity = Category::class;
}
