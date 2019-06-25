<?php
namespace execut\import\migrations;
class m160802_130555_addFilesStatuses extends \execut\yii\migration\Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('import_files_statuses')->batchInsert([
            'id',
            'key',
            'name'
        ], [
            [
                7,
                'stop',
                'Остановить',
            ],
            [
                8,
                'stoped',
                'Остановлен',
            ],
        ]);
    }
}
