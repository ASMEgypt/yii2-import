<?php
class m161004_065553_dropDefaultValuesFromSettings extends \execut\yii\migration\Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('import_settings')
            ->alterColumnDropDefault('ftp_port', 21)
            ->alterColumnDropNotNull('ftp_port')
            ->alterColumnDropDefault('ftp_timeout', 60)
            ->alterColumnDropNotNull('ftp_timeout')
            ->alterColumnDropDefault('site_auth_url', '/')
            ->alterColumnDropNotNull('site_auth_url')
            ->alterColumnDropDefault('site_auth_method', 'post')
            ->alterColumnDropNotNull('site_auth_method')
        ;
    }
}
