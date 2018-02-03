<?php
namespace App\Blog\Table;

use Virton\Database\Table;
use App\Blog\Entity\Post;
use Virton\Database\Query;

class PostTable extends Table
{
    protected $entity = Post::class;

    protected $table = 'posts';

    public function findPublic(): Query
    {
        return $this->findAll()
            ->where('p.published = 1')
            ->where('p.created_at < NOW()');
    }

    public function findAll(): Query
    {
        $category = (new CategoryTable($this->pdo))->getTable();
        return $this->makeQuery()
            ->select("p.*, c.name AS category_name, c.slug AS category_slug")
            ->join("$category AS c", "c.id = p.category_id")
            ->order("p.created_at DESC")
        ;
    }

    /**
     * @param int $postId
     * @return object|Post
     */
    public function findWithCategory(int $postId): Post
    {
        return $this->findPublic()->where("p.id = $postId")->fetch();
    }

    public function findPublicForCategory(int $categoryId): Query
    {
        return $this->findPublic()->where("p.category_id = $categoryId");
    }
}
