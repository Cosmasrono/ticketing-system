<?php
use yii\helpers\Html;

/* @var $user app\models\User */

?>
<div class="verification-email">
    <p>Hello <?= Html::encode($user->name) ?>,</p>

    <p>Thank you for signing up as a Super Admin. Please click the link below to verify your email address:</p>

    <p><?= Html::a('Verify Email', ['site/verify-email', 'token' => $user->verification_token]) ?></p>

    <p>If you did not sign up for this account, you can ignore this email.</p>

    <p>Best regards,<br><?= Html::encode(Yii::$app->name) ?></p>
</div> 