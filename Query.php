<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/20/17
 * Time: 12:18 PM
 */

namespace execut\import;


interface Query
{
    public function byImportAttributes($attributes);
}