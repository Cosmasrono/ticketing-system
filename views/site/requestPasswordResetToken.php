<?php
// views/site/requestPasswordResetToken.php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request Password Reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>
                    <p class="text-center mb-4">Please enter your company email. A password reset link will be sent there.</p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'request-password-reset-form',
                        'options' => ['class' => 'form-vertical'],
                    ]); ?>

                    <?= $form->field($model, 'company_email', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Enter your company email',
                        'class' => 'form-control'
                    ]) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Send Reset Link', [
                            'class' => 'btn btn-primary btn-block'
                        ]) ?>
                    </div>

                    <div class="text-center mt-3">
                        <?= Html::a('Back to login', ['site/login'], [
                            'class' => 'text-muted text-decoration-none'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success mt-3">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger mt-3">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-request-password-reset {
        padding: 40px 0;
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
    .card-body {
        padding: 30px;
    }
    .form-control {
        height: 45px;
    }
    .btn-primary {
        height: 45px;
        font-size: 16px;
        width: 100%;
    }
    .alert {
        margin-bottom: 20px;
    }
    .text-decoration-none:hover {
        text-decoration: underline !important;
    }
CSS;
$this->registerCss($css);
?>