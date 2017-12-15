<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 8/1/17
 * Time: 11:57 AM
 */

namespace execut\import;


use execut\import\models\File;

interface Plugin
{
    /**
     * Возвращает список query для разных моделей
     *
     * @return array
     */
    public function getDictionaries();

    /**
     * Возвращает настройки парсеров моделей
     *
     * @return array
     */
    public function getParsersByTypesSettings();

    /**
     * Удаляет привязанные к файлу импорта записи
     */
    public function deleteRelatedRecords(File $importFile);

    public function getAllowedRoles();

    public function getFilesCrudFieldsPlugins();

    public function getAttributesSetsTypesList();

    public function getAttributesValuesTypesList();

    public function getSettingsCrudFieldsPlugins();
}