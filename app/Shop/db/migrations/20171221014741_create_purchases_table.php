<?php

use Phinx\Migration\AbstractMigration;

class CreatePurchasesTable extends AbstractMigration
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
        $this->table('purchases')
            ->addColumn('user_id', 'integer')
            ->addForeignKey('user_id', 'users', 'id')
            ->addColumn('product_id', 'integer')
            ->addForeignKey('product_id', 'products', 'id')
            ->addColumn('price', 'float', ['precision' => 6, 'scale' => 2])
            ->addColumn('vat', 'float', ['precision' => 6, 'scale' => 2])
            ->addColumn('country', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('stripe_id', 'string')
            ->create();
    }
}
