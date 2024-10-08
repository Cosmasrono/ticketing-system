<?php
use yii\helpers\Html;

$this->title = 'Email Verification Successful';
?>
<div class="site-verify-email-success">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="alert alert-success">
        Your email has been successfully verified. You can now log in to your account.
    </div>
    <p>
        <?= Html::a('Go to Login', ['site/login'], ['class' => 'btn btn-primary']) ?>
    </p>
</div>
