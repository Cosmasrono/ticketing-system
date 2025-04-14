<?php
use yii\helpers\Html;
?>

<div class="user-credentials">
    <h2>Welcome to <?= Yii::$app->name ?></h2>

    <p>Dear <?= Html::encode($user->name) ?>,</p>

    <p>Your account has been created for <?= Html::encode($company) ?>. Here are your login details:</p>

    <ul>
        <li>Email: <?= Html::encode($user->email) ?></li>
        <li>Password: <?= Html::encode($password) ?></li>
        <li>Login URL: <?= Html::encode(Yii::$app->urlManager->createAbsoluteUrl(['site/login'])) ?></li>
    </ul>

    <p>For security reasons, please change your password after your first login.</p>

    <p>If you have any questions, please contact your system administrator.</p>

    <p>Best regards,<br>
    <?= Yii::$app->name ?> Team</p>
</div> 