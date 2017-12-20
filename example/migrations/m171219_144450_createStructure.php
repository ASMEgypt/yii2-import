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
        $i->table('example_products')
            ->create($this->defaultColumns([
                'name' => $this->string()->notNull(),
                'price' => $this->float(2),
            ]));
    }
}
