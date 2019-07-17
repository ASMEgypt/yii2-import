<?php
namespace execut\import\migrations;
use execut\yii\migration\Migration;
use execut\yii\migration\Inverter;

class m190717_065149_addAttachmentNameField extends Migration
{
    public function initInverter(Inverter $i)
    {
        $i->table('import_settings')
            ->addColumn('email_attachment_template', $this->string());
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
