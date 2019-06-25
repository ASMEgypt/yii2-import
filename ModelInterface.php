<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/17
 * Time: 2:39 PM
 */

namespace execut\import;


interface ModelInterface
{
    public function getImportUniqueKeys($attributesNames, $whereValues);
}