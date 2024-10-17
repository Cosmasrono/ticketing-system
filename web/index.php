<?php

// Temporarily enable debugging
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');


//production
// Revert to production settings
// defined('YII_DEBUG') or define('YII_DEBUG', false);
// defined('YII_ENV') or define('YII_ENV', 'prod');

// ... rest of the file remains unchanged ...

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
