<?php
class m160727_115711_addProgressColumns extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('import_files')
            ->addColumn('rows_count', $this->integer())
            ->addColumn('rows_errors', $this->integer())
            ->addColumn('rows_success', $this->integer())
            ->addColumn('start_date', $this->dateTime())
            ->addColumn('end_date', $this->dateTime());
        $i->createTable('import_logs', array_merge($this->defaultColumns(), [
            'level' => $this->integer()->notNull(),
            'category' => $this->string()->notNull(),
            'prefix' => $this->string(),
            'message' => $this->text(),
            'row_nbr' => $this->integer(),
            'column_nbr' => $this->integer(),
            'value' => $this->string(),
        ]));

        $i->table('import_logs')
            ->addForeignColumn('import_files', true)
            ->addForeignColumn('import_settings_values');
    }
}
