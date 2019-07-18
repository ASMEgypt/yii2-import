<?php
namespace execut\import\migrations;
class m161004_065553_dropDefaultValuesFromSettings extends \execut\yii\migration\Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('import_settings')
            ->alterColumnDropDefault('ftp_port', 21)
            ->alterColumnDropNotNull('ftp_port')
            ->update(['ftp_port' => 21], 'ftp_port is null')
            ->alterColumnDropDefault('ftp_timeout', 60)
            ->alterColumnDropNotNull('ftp_timeout')
            ->update(['ftp_timeout' => 60], 'ftp_timeout is null')
            ->alterColumnDropDefault('site_auth_url', '\'/\'')
            ->alterColumnDropNotNull('site_auth_url')
            ->update(['site_auth_url' => ''], 'site_auth_url is null')
            ->alterColumnDropDefault('site_auth_method', '\'post\'')
            ->alterColumnDropNotNull('site_auth_method')
            ->update(['site_auth_method' => ''], 'site_auth_method is null')
        ;
    }
}
