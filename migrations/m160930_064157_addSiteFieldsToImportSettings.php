<?php
namespace execut\import\migrations;
class m160930_064157_addSiteFieldsToImportSettings extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $is = $i->table('import_settings');

        $is->addColumn('site_host', $this->string())
            ->addColumn('site_auth_url', $this->string())
            ->addColumn('site_auth_method', $this->string())
            ->addColumn('site_login_field', $this->string())
            ->addColumn('site_password_field', $this->string())
            ->addColumn('site_other_fields', $this->string(1000))
            ->addColumn('site_login', $this->string())
            ->addColumn('site_password', $this->string())
            ->addColumn('site_file_url', $this->string())
            ->alterColumnSetDefault('site_auth_method', '\'post\'')
            ->alterColumnSetDefault('site_auth_url', '\'/\'')
            ->update([
                'site_auth_method' => 'post',
                'site_auth_url' => '/'
            ])
            ->alterColumnSetNotNull('site_auth_method')
            ->alterColumnSetNotNull('site_auth_url');
        $is->update([
            'import_files_source_id' => 2,
        ], [
            'import_files_source_id' => [4],
        ]);
    }
}
