<?php
namespace execut\import\migrations;
class m160929_071905_addFtpFieldsToSettings extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $is = $i->table('import_settings');

        $is->addColumn('ftp_host', $this->string())
            ->addColumn('ftp_ssl', $this->boolean())
            ->addColumn('ftp_port', $this->integer())
            ->addColumn('ftp_timeout', $this->integer())
            ->addColumn('ftp_login', $this->string())
            ->addColumn('ftp_password', $this->string())
            ->addColumn('ftp_dir', $this->string())
            ->addColumn('ftp_file_name', $this->string())
            ->alterColumnSetDefault('ftp_timeout', 60)
            ->update([
                'ftp_timeout' => 60,
                'ftp_ssl' => false,
                'ftp_port' => 21,
            ])
            ->alterColumnSetNotNull('ftp_timeout')
            ->alterColumnSetDefault('ftp_ssl', 'false')
            ->alterColumnSetNotNull('ftp_ssl')
            ->alterColumnSetDefault('ftp_port', 21)
            ->alterColumnSetNotNull('ftp_port');
        $i->table('import_files_sources')->batchInsert([
                'id',
                'name',
                'key',
            ], [
                [
                    3,
                    'FTP',
                    'ftp',
                ],
                [
                    4,
                    'Сайт',
                    'site',
                ],
            ]);
        $is->update([
            'import_files_source_id' => 2,
        ], [
            'import_files_source_id' => [3,4],
        ]);
    }
}
