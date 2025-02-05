<?php
use yii\helpers\Html;

/* @var $user app\models\User */
/* @var $verifyLink string */
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Welcome to <?= Yii::$app->name ?>!</h2>
    
    <p>Dear <?= Html::encode($user->name) ?>,</p>
    
    <p>Thank you for registering. To complete your registration, please verify your email address by clicking the button below:</p>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= $verifyLink ?>" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
            Verify Email Address
        </a>
    </p>
    
    <p>Or copy and paste this URL into your browser:</p>
    <p style="word-break: break-all;"><?= $verifyLink ?></p>
    
    <p>If you did not create an account, no further action is required.</p>
    
    <p>Best regards,<br>
    <?= Yii::$app->name ?> Team</p>
</div> 