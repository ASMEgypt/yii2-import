<?php
/**
 * Class m171219_132916_dropOldColumns
 */
class m171219_132916_dropOldColumns extends \execut\yii\migration\Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->dropColumn('import_settings', 'csv_delimiter_old', $this->char());
        $i->dropColumn('import_settings', 'csv_enclosure_old', $this->char());
    }
}
