<?php

/* @var $this yii\web\View */
/* @var $user app\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->username ?>,

You have requested to reset your password. Please click the link below to set a new password:

<?= $resetLink ?>

If you did not request a password reset, please ignore this email.

This is an automated message. Please do not reply to this email.
