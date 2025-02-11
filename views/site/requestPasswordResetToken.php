<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php elseif (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php else: ?>
        <p>Please fill out your email. A link to reset password will be displayed.</p>

        <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

            <?= $form->field($model, 'company_email')->textInput(['autofocus' => true]) ?>

            <div class="form-group">
                <?= Html::submitButton('Send', ['class' => 'btn btn-primary']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>
</div>

<style>
/* Orange-themed Request Password Reset Form Styles */
.site-request-password-reset {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #FFF3E0; /* Light orange background */
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.site-request-password-reset h1 {
    color: #FF9800; /* Orange */
    text-align: center;
    margin-bottom: 20px;
}

.site-request-password-reset p {
    color: #F57C00; /* Dark orange */
    margin-bottom: 20px;
}

.form-group {
</style>