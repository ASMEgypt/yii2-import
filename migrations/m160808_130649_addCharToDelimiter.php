<?php
namespace execut\import\migrations;
class m160808_130649_addCharToDelimiter extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->renameColumn('import_settings', 'csv_enclosure', 'csv_enclosure_old');
        $this->addColumn('import_settings', 'csv_enclosure', 'varchar(255)');
        $this->renameColumn('import_settings', 'csv_delimiter', 'csv_delimiter_old');
        $this->addColumn('import_settings', 'csv_delimiter', 'varchar(255)');

        $this->update('import_settings', [
            'csv_enclosure' => new \yii\db\Expression('csv_enclosure_old'),
        ]);

        $this->update('import_settings', [
            'csv_delimiter' => new \yii\db\Expression('csv_delimiter_old'),
        ]);
    }
    public function safeDown()
    {
        $this->dropColumn('import_settings', 'csv_enclosure_old');
        $this->dropColumn('import_settings', 'csv_delimiter_old');
    }
}
