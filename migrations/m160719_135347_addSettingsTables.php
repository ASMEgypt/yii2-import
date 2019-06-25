<?php
namespace execut\import\migrations;
class m160719_135347_addSettingsTables extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->createTable('import_settings', array_merge($this->defaultColumns(), [
            'name' => $this->string()->notNull(),
            'ignored_lines' => $this->integer()->notNull(),
            'email' => $this->string(),
            'email_title_match' => $this->string(),
            'csv_delimiter' => $this->char(1),
            'csv_enclosure' => $this->char(1),
        ]));
//        $i->createTable('import_files_types', array_merge($this->defaultColumns(), [
//            'name' => $this->string()->notNull(),
//            'key' => $this->string()->notNull(),
//        ]));
//        $i->batchInsert('import_files_types', [
//            'id',
//            'name',
//            'key'
//        ], [
//            [
//                'id' => 1,
//                'key' => 'auto',
//                'name' => 'auto'
//            ],
//            [
//                'id' => 1,
//                'name' => 'csv',
//                'key' => 'csv',
//            ],
//            [
//                'id' => 2,
//                'name' => 'txt',
//                'key' => 'txt',
//            ],
//            [
//                'id' => 3,
//                'name' => 'xls',
//                'key' => 'xls',
//            ],
//            [
//                'id' => 4,
//                'name' => 'xlsx',
//                'key' => 'xlsx',
//            ],
//            [
//                'id' => 5,
//                'name' => 'xlsm',
//                'key' => 'xlsm',
//            ],
//        ]);
//        $i->createTable('import_files_encodings', array_merge($this->defaultColumns(), [
//            'name' => $this->string()->notNull(),
//        ]));
//        $i->batchInsert('import_files_encodings', [
//            'id',
//            'key',
//            'name',
//        ], [
//            [
//                'id' => 1,
//                'key' => 'auto',
//                'name' => 'auto'
//            ],
//            [
//                'id' => 2,
//                'key' => 'utf-8',
//                'name' => 'utf-8'
//            ],
//            [
//                'id' => 3,
//                'key' => 'utf-16',
//                'name' => 'utf-16'
//            ],
//            [
//                'id' => 4,
//                'key' => 'KOI8-R',
//                'name' => 'KOI8-R'
//            ],
//            [
//                'id' => 5,
//                'key' => 'KOI8-U',
//                'name' => 'KOI8-U'
//            ],
//            [
//                'id' => 6,
//                'key' => 'windows-1251',
//                'name' => 'windows-1252'
//            ],
//        ]);
        $i->table('import_settings')
            ->addForeignColumn('import_files_sources', true);
        $i->delete('import_files');
        $i->table('import_files')
            ->addForeignColumn('import_settings', true);
//            ->addForeignColumn('import_files_types')
//            ->addForeignColumn('import_files_encodings');
        $i->createTable('import_settings_sheets', array_merge($this->defaultColumns(), [
            'name' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
        ]));

        $i->table('import_settings_sheets')->addForeignColumn('import_settings', true);

        $i->createTable('import_settings_sets', array_merge($this->defaultColumns(), [
            'type' => $this->string()->notNull(),
        ]));

        $i->table('import_settings_sets')->addForeignColumn('import_settings_sheets', true);

        $i->createTable('import_settings_values', array_merge($this->defaultColumns(), [
            'type' => $this->string()->notNull(),
            'column_nbr' => $this->string(),
            'format' => $this->string(),
            'value_string' => $this->string(),
            'value_option' => $this->string(),
        ]));

        $i->table('import_settings_values')->addForeignColumn('import_settings_sets', true);
    }
}
