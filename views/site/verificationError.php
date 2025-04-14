<?php
use yii\helpers\Html;

$this->title = 'Email Verification Error';
?>
<div class="site-verify-email-error">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>
    <p>
        If you continue to experience issues, please contact our support team.
    </p>
</div>
