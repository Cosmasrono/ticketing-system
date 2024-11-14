<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@rules' => '@app/rules',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'your-secret-key',
            'enableCsrfValidation' => true,
            // 'enableCsrfCookie' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'dsn' => 'sendinblue+api://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@default',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin/<id:\d+>' => 'site/admin',
                'ticket/view/<id:\d+>' => 'ticket/view',
                'developer/add-comment' => 'developer/add-comment',
                'developer/close-ticket' => 'developer/close-ticket',
                'ticket/approve/<id:\d+>' => 'ticket/approve',
                'ticket/assign/<id:\d+>' => 'ticket/assign',
                'ticket/assign' => 'ticket/assign',
                'ticket/cancel/<id:\d+>' => 'ticket/cancel',
                'client/create' => 'client/create',
                'ticket/index' => 'ticket/index',
                'ticket/reopen/<id:\d+>' => 'ticket/reopen',
                'ticket/reopen' => 'ticket/reopen',
                'ticket/close' => 'ticket/close',
            ],
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => ['httponly' => true],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => 'db', // DB connection component or its config 
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
        ],
        'timeZone' => 'Africa/Nairobi', // Set your timezone
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'defaultTimeZone' => 'Africa/Nairobi',
            'timeZone' => 'Africa/Nairobi',
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'timeFormat' => 'php:H:i:s',
        ],
    ],
    'params' => $params,
    'modules' => [
        // 'admin' => [
        //     'class' => 'app\modules\admin\Module',
        // ],
    ],
];

// Temporarily enable debug module
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment and adjust the following line to restrict access to your IP
        //'allowedIPs' => ['127.0.0.1', '::1', 'your.ip.address.here'],
    ];
}

return $config;
