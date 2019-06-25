<?php
namespace execut\import\migrations;
class m160801_142638_addEncodingsTable extends \execut\yii\migration\Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->createTable('import_files_encodings', array_merge($this->defaultColumns(), [
            'name' => $this->string()->notNull(),
            'key' => $this->string()->notNull(),
        ]));
        $i->batchInsert('import_files_encodings', [
            'id',
            'key',
            'name',
        ], [
            [
                'id' => 1,
                'key' => 'UTF-8',
                'name' => 'UTF-8'
            ],
            [
                'id' => 2,
                'key' => 'UTF-16',
                'name' => 'UTF-16'
            ],
            [
                'id' => 3,
                'key' => 'KOI8-R',
                'name' => 'KOI8-R'
            ],
            [
                'id' => 4,
                'key' => 'KOI8-U',
                'name' => 'KOI8-U'
            ],
            [
                'id' => 5,
                'key' => 'Windows-1251',
                'name' => 'Windows-1251'
            ],
            [
                'id' => 6,
                'key' => 'Windows-1252',
                'name' => 'Windows-1252'
            ],
        ]);
        $i->table('import_settings')
            ->addForeignColumn('import_files_encodings')
            ->update(['import_files_encoding_id' => 1])
            ->alterColumnSetNotNull('import_files_encoding_id');
    }
}
