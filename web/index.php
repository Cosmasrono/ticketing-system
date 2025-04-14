<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// Disable SSL verification globally
stream_context_set_default([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

// Load the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
require __DIR__ . '/../config/helpers.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    // Handle error if .env file is not found
    error_log('Error loading .env file: ' . $e->getMessage());
}

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();