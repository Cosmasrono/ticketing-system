<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'debug', 'gii'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'your-secret-key',
            'enableCsrfValidation' => true,
            'class' => 'yii\web\Request',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],


        'brevoMailer' => [
            'class' => 'app\components\BrevoMailer',

                'apiKey'=> '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',
            
        ],
        
        
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'class' => 'yii\web\User',
        ],
      'mailer' => [
    'class' => 'yii\symfonymailer\Mailer',
    'viewPath' => '@app/mail',
    'useFileTransport' => false,
    'transport' => [
        'dsn' => 'sendinblue+api://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@default',
    ],

        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => ['email-verification'],
                    'logFile' => '@runtime/logs/email-verification.log',
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
                'site/verify-email' => 'site/verify-email',
                'verify-email/<token:\w+>' => 'site/verify-email',

            ],
        ],
     
    ],
    'params' => array_merge($params, [
        'senderEmail' => 'francismwaniki630@gmail.com',
        'senderName' => 'Iansoft',
    ]),
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
    ];
}

$config['controllerMap'] = [
    'developer' => 'app\controllers\DeveloperController',
];

return $config;
