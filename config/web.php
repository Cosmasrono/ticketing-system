<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'your-secret-key',
            'enableCsrfValidation' => true, // Make sure this is true or remove this line
        ],
        // 'defaultRoute' => 'site/index',
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'viewPath' => '@app/mail',  // This should point to your mail directory
            'useFileTransport' => false,  // Set to true for development to store emails as files
            'transport' => [
                'dsn' => 'smtp://username:password@smtp.example.com:587',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => ['ticket'],
                ],
            ],
        ],
        'db' => $db,
      
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'site/index' => 'site/index',
                'login' => 'site/login',
                'site/approve-ticket' => 'site/approve-ticket',

                // 'login' => 'site/login',
                // 'signup' => 'site/signup',
                // 'ticket/assign/<id:\d+>' => 'ticket/assign',
                'developer/<id:\d+>' => 'site/developer-dashboard',
                'developer/close-ticket/<id:\d+>' => 'developer/close-ticket',
                'ticket/approve/<id:\d+>' => 'ticket/approve',
                'site/reset-password/<token:[\w-]+>' => 'site/reset-password',
                'site/reset' => 'site/reset',
                'site/reset/<token:[\w-]+>' => 'site/reset',
            ],
        ],
     
    ],
    'params' => [
        'adminEmail' => 'ccosmas001@gmail.com', // Add this line with the email you want to use
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

$config['controllerMap'] = [
    'developer' => 'app\controllers\DeveloperController',
];

return $config;
