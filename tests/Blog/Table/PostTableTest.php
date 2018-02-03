<?php
namespace Tests\App\Blog\Table;

use Tests\DatabaseTestCase;

use App\Blog\Entity\Post;
use App\Blog\Table\PostTable;
use Virton\Database\NoRecordException;
use PDO;

class PostTableTest extends DatabaseTestCase
{
    /**
     * @var PostTable
     */
    private $postTable;

    public function setUp()
    {
        parent::setUp();
        $pdo = $this->getPDO();
        $this->migrateDatabase($pdo);
        $this->postTable = new PostTable($pdo);
    }

    public function testFind()
    {
        $this->seedDatabase($this->postTable->getPdo());
        $count = (int) $this->postTable->getPdo()->query("SELECT COUNT(id) FROM posts")->fetchColumn();
        $this->assertEquals(100, $count);
        $post = $this->postTable->find(1);
        $this->assertInstanceOf(Post::class, $post);
    }

    public function testFindNotFoundRecord()
    {
        $this->seedDatabase($this->postTable->getPdo());
        $this->expectException(NoRecordException::class);
        $this->postTable->find(101);
    }

    public function testUpdate()
    {
        $this->seedDatabase($this->postTable->getPdo());
        $this->postTable->update(1, ['name' => 'Hello', 'slug' => 'test']);
        $post = $this->postTable->find(1);
        $this->assertEquals('Hello', $post->getName());
        $this->assertEquals('test', $post->getSlug());
    }

    public function testInsert()
    {
        $this->postTable->insert(['name' => 'Hello', 'slug' => 'test']);
        $post = $this->postTable->find(1);
        $this->assertEquals('Hello', $post->getName());
        $this->assertEquals('test', $post->getSlug());
    }

    public function testDelete()
    {
        $this->postTable->insert(['name' => 'Hello', 'slug' => 'test']);
        $this->postTable->insert(['name' => 'Hello2', 'slug' => 'test-2']);
        $count = $this->postTable->getPdo()->query('SELECT COUNT(id) FROM posts')->fetchColumn();
        $this->assertEquals(2, (int)$count);

        $this->postTable->delete($this->postTable->getPdo()->lastInsertId());
        $count = $this->postTable->getPdo()->query('SELECT COUNT(id) FROM posts')->fetchColumn();
        $this->assertEquals(1, (int)$count);
    }
}
