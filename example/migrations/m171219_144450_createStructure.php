<?php
use execut\yii\migration\Migration;

/**
 * Class m171219_144450_createStructure
 */
class m171219_144450_createStructure extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('example_brands')
            ->create($this->defaultColumns([
                'name' => $this->string()->notNull(),
            ]));
        $i->table('example_articles')
            ->create($this->defaultColumns([
                'article' => $this->string()->notNull(),
                'source' => $this->string(),
            ]))
            ->addForeignColumn('example_brands', true);
        $i->table('example_brands_aliases')
            ->create($this->defaultColumns([
                'name' => $this->string()->notNull(),
            ]))
            ->addForeignColumn('example_brands', true);
        $i->table('example_products')
            ->create($this->defaultColumns([
                'name' => $this->string()->notNull(),
                'price' => $this->float(2),
            ]))
            ->addForeignColumn('example_articles');
    }
}
