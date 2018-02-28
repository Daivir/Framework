<?php


use Phinx\Migration\AbstractMigration;

class CreateCartTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $constraints = ['delete' => 'cascade'];
        $this->table('carts')
            ->addColumn('user_id', 'integer')
            ->addForeignKey('user_id', 'users', 'id', $constraints)
            ->create();
        
        $this->table('carts_products')
            ->addColumn('cart_id', 'integer')
            ->addColumn('product_id', 'integer')
            ->addColumn('quantity', 'integer', ['default' => 1])
            ->addForeignKey('cart_id', 'carts', 'id', $constraints)
            ->addForeignKey('product_id', 'products', 'id', $constraints)
            ->create();
    }
}
