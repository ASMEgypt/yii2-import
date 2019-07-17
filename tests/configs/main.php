<?php
$params = [];

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
//        'cache' => [
//            'class' => 'yii\caching\FileCache',
//        ],
        'i18n' => [
//            'translations' => [
//                'app'=>[
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => "@app/messages",
//                    'sourceLanguage' => 'en_US',
//                    'fileMap' => [
//                        'app'=>'app.php',
//                    ]
//                ],
//                'common.modules.catalog.models' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => '@app/modules/catalog/messages',
//                    'sourceLanguage' => 'en',
//                    'fileMap' => [
//                        'common.modules.catalog.models' => 'models.php',
//                    ],
//                ],
//            ],
        ],
    ],
    'params' => $params,
];
