<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Iansoft',
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

        'timezone'=>'UTC',
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'your-secret-key',
            'enableCsrfValidation' => true,
            // 'enableCsrfCookie' => true,
        ],

     
    'cloudinary' => [
        'class' => 'app\components\CloudinaryComponent',
        'cloud_name' => 'dscbboswt',
        'api_key' => '165833264188614',
        'api_secret' => 'LfgHbl18_gohlTycrjr3OdQvEWE',
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
                'site/first-login' => 'site/first-login',
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
                'change-password' => 'site/change-password',
                'create-user' => 'site/create-user',
                'ticket/upload-to-cloudinary' => 'ticket/upload-to-cloudinary',
                'set-initial-password/<token:[\w\-]+>/<email:.*>' => 'site/set-initial-password',
                'set-initial-password' => 'site/set-initial-password',
                'site/change-initial-password' => 'site/change-initial-password',
                'site/reset-password' => 'site/reset-password',
                'site/create-user-for-company' => 'site/create-user-for-company',
                'developer/view/<id:\d+>' => 'developer/view',
                'ticket/search' => 'ticket/search',
                'site/update-renewal-status' => 'site/update-renewal-status',
                'profile/<id:\d+>' => 'site/profile',
                'user-profile/<id:\d+>' => 'user-profile/view',
                'admin-profile/<id:\d+>' => 'admin-profile/view',
            ],
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => ['httponly' => true],
            'timeout' => 2400, // 40 minutes in seconds
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
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'defaultTimeZone' => 'Africa/Nairobi',
            'timeZone' => 'UTC',
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'timeFormat' => 'php:H:i:s',
        ],
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                [
                    'actions' => ['create-user'],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->identity->role === 'admin';
                    }
                ],
                // ... other rules ...
            ],
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if (isset($_SERVER['HTTP_ORIGIN'])) {
                    // Allow only specific origins
                    $allowedOrigins = ['http://localhost:8080', 'http://localhost:3000'];
                    if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
                        $response->headers->set('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN']);
                    }
                }
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-Token');
                $response->headers->set('Access-Control-Allow-Credentials', 'false');
            },
        ],
    ],
    'params' => $params,
    'modules' => [
        // 'admin' => [
        //     'class' => 'app\modules\admin\Module',
        // ],
    ],
    'defaultRoute' => 'site/index',
    'timeZone' => 'UTC',
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