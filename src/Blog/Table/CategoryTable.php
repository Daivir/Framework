<?php
namespace App\Blog\Table;

use App\Blog\Entity\Category;
use Framework\Database\Query;
use Framework\Database\QueryResult;
use Framework\Database\Table;

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
