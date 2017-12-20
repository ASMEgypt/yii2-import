<?php
class m160901_104452_addDelimiterTypeToImport extends \execut\yii\migration\Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('import_settings_values')
            ->addColumn('number_delimiter', $this->string())
            ->alterColumnSetDefault('number_delimiter', '\'.\'')
            ->update([
                'number_delimiter' => '.',
            ])
            ->alterColumnSetNotNull('number_delimiter');
    }
}
