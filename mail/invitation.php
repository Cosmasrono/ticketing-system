<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model app\models\Invitation */
/* @var $user app\models\User */

echo "Welcome to " . Yii::$app->name . "\n\n";

echo "Hello " . Html::encode($model->company_name) . ",\n\n";

echo "You have been invited to join " . Yii::$app->name . ". Click the link below to set up your account:\n\n";

echo Url::to(['site/complete-registration', 
    'token' => $model->token,
    'email' => $model->company_email
], true) . "\n\n";

echo "Important: This invitation will expire in 1 hour.\n\n";

echo "Best regards,\n";
echo "The " . Yii::$app->name . " Team"; 