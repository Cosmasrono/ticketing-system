<?php
use yii\helpers\Html;

$this->title = 'Email Verification';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-verify-email">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php elseif (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p>Your email has been successfully verified. You can now use all features of our website.</p>
        <p><?= Html::a('Go to Login', ['site/login'], ['class' => 'btn btn-primary']) ?></p>
    <?php else: ?>
        <p>There was a problem verifying your email. Please check if you have used the correct verification link.</p>
        <p>If you continue to have issues, please contact our support team.</p>
    <?php endif; ?>
</div>
