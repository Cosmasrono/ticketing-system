<?php

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $resetLink string */

?>
Hello Administrator,

A password reset has been requested for the user: <?= $user->company_email ?>

Follow the link below to reset the password:

<?= $resetLink ?>
