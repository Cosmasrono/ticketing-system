<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

ini_set('curl.cainfo', '');

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
        'timezone' => 'UTC',
        'request' => [
            'class' => 'yii\web\Request',
            'cookieValidationKey' => 'your-existing-key',
            'parsers' => [
                'multipart/form-data' => 'yii\web\MultipartFormDataParser'
            ]
        ],
        'uploadedFile' => [
            'class' => 'yii\web\UploadedFile',
            'tempPath' => __DIR__ . '/../runtime/uploads/temp',
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
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
            'useFileTransport' => false, // Set to false to send real emails
            'transport' => [
                // 'scheme' => 'smtp',
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com', // Brevo's SMTP relay
                'username' => 'abby.wairi@gmail.com', // Always use 'apikey' as the username
                'password' => 'mgjnsigzwttosnys', // Fetch API key from environment variables
                'port' => 587, // Use 465 for SSL, 25 as an alternative
                'encryption' => 'tls', // Use 'ssl' if using port 465
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
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => '',
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
                'user/profile/<id:\d+>' => 'user-profile/view',
                'admin-profile/<id:\d+>' => 'admin-profile/view',
                'contract-renewal/<action>' => 'contract-renewal/<action>',
                'site/renew-contract/<id:\d+>' => 'site/renew-contract',
            ],
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => ['httponly' => true],
            'timeout' => 2400, // 40 minutes in seconds
            'savePath' => dirname(__DIR__) . '/runtime/sessions'
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
                    $allowedOrigins = ['http://localhost:8082', 'http://localhost:3000'];
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
