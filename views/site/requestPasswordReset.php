<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PasswordResetRequestForm $model */

$this->title = 'Request Password Reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-5 text-center">
                    <div class="text-center mb-2">
                        <a href="<?= Yii::$app->homeUrl ?>">
                            <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Reset Password" class="img-fluid" style="max-width: 100px;">
                        </a>
                    </div>
                    <h2 class="text-center mb-4"><?= Html::encode($this->title) ?></h2>

                    <p class="text-muted text-center mb-4">
                        Please enter your email. A link to reset password will be sent there.
                    </p>

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
                            'class' => 'btn btn-primary btn-block',
                            'name' => 'reset-button'
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

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger mt-3">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success mt-3">
                    <?= Yii::$app->session->getFlash('success') ?>
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