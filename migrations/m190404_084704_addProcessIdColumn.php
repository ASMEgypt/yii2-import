<?php
namespace execut\import\migrations;
use execut\yii\migration\Migration;
use execut\yii\migration\Inverter;

class m190404_084704_addProcessIdColumn extends Migration
{
    public function initInverter(Inverter $i)
    {
        $i->table('import_files')
            ->addColumn('process_id', $this->integer());
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
