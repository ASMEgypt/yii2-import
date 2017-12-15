<?php
/**
 * User: execut
 * Date: 03.08.16
 * Time: 13:47
 */

namespace execut\import\components;

use yii\filters\AccessControl;
use yii\web\Controller;

class WebController extends Controller
{
    protected $_roles = ['import_manager'];
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => $this->_roles,
                    ],
                ],
            ],
        ];
    }
}