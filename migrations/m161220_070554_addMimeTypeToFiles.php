<?php
class m161220_070554_addMimeTypeToFiles extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $types = $i->table('import_settings')
            ->addColumn('is_check_mime_type', $this->boolean())
            ->alterColumnSetDefault('is_check_mime_type', 'true')
            ->update([
                'is_check_mime_type' => true,
            ])
            ->alterColumnSetNotNull('is_check_mime_type');
    }
}
