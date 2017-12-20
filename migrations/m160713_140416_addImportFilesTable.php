<?php
class m160713_140416_addImportFilesTable extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i = $this->inverter;
        $i->createTable('import_files', array_merge($this->defaultColumns(), [
            'name' => $this->string()->notNull(),
            'extension' => $this->string()->notNull(),
            'mime_type' => $this->string()->notNull(),
            'content' => $this->data()->notNull(),
            'md5' => $this->string(64)->notNull(),
        ]));
        $i->createTable('import_files_sources', array_merge($this->defaultColumns(), [
            'name' => $this->string()->notNull(),
            'key' => $this->string()->notNull(),
        ]));
        $i->batchInsert('import_files_sources', [
            'id',
            'name',
            'key',
        ], [
            [
                'id' => 1,
                'name' => 'Email',
                'key' => 'email',
            ],
            [
                'id' => 2,
                'name' => 'Вручную',
                'key' => 'manual',
            ],
        ]);

        $i->createTable('import_files_statuses', array_merge($this->defaultColumns(), [
            'name' => $this->string()->notNull(),
            'key' => $this->string()->notNull(),
        ]));


        $i->table('import_files')
            ->addForeignColumn('import_files_sources', true)
            ->addForeignColumn('user')
            ->addForeignColumn('import_files_statuses', true);
        $i->batchInsert('import_files_statuses', [
            'id',
            'name',
            'key',
        ], [
            [
                'id' => 1,
                'name' => 'New',
                'key' => 'new',
            ],
            [
                'id' => 2,
                'name' => 'Reload',
                'key' => 'reload',
            ],
            [
                'id' => 3,
                'name' => 'Delete',
                'key' => 'delete',
            ],
            [
                'id' => 4,
                'name' => 'Loaded',
                'key' => 'loaded',
            ],
            [
                'id' => 5,
                'name' => 'Loading',
                'key' => 'loading',
            ],
            [
                'id' => 6,
                'name' => 'Deleting',
                'key' => 'deleting',
            ]
        ]);
    }
}
