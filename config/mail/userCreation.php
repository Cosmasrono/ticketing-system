<?php
use yii\helpers\Html;

/* @var $user app\models\User */
?>

<div class="user-creation">
    <h2>Welcome to <?= Yii::$app->name ?>!</h2>

    <p>Dear <?= Html::encode($user->name) ?>,</p>

    <p>Your account has been created with the following details:</p>

    <ul>
        <li>Email: <?= Html::encode($user->email) ?></li>
        <li>Company: <?= Html::encode($user->company_name) ?></li>
        <li>Role: <?= Html::encode($user->role) ?></li>
    </ul>

    <p>You can now log in to your account using your email and the password provided by your administrator.</p>

    <p>For security reasons, we recommend changing your password after your first login.</p>

    <p>If you have any questions, please contact your system administrator.</p>

    <p>Best regards,<br>
    <?= Yii::$app->name ?> Team</p>
</div> 