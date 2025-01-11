<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $verificationLink string */
?>
<div class="verify-email">
    <p>Hello <?= Html::encode($user->name) ?>,</p>

    <p>Thank you for signing up. Please click the link below to verify your email:</p>

    <p><?= Html::a(Html::encode($verificationLink), $verificationLink) ?></p>

    <p>If you didn't create an account, you can ignore this email.</p>
</div>
