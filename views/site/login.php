<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login d-flex align-items-center justify-content-center">
    <div class="col-lg-4">
        <div class="card shadow-lg border-0 rounded-0">
            <div class="card-body p-5">
                <div class="text-center mb-2">
                    <a href="<?= Yii::$app->homeUrl ?>">
                        <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Reset Password" class="img-fluid" style="max-width: 100px;">
                    </a>
                </div>
                <h2 class="text-center mb-3 fw-semibold">
                    <?= Html::encode($this->title) ?>
                </h2>

                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['class' => 'needs-validation', 'novalidate' => 'true'],
                ]); ?>

                <?= $form->field($model, 'company_email', [
                    'inputOptions' => [
                        'class' => 'form-control rounded-sm',
                        'placeholder' => 'Company Email',
                        'autofocus' => true,
                    ],
                ])->label(false) ?>

                <?= $form->field($model, 'password', [
                    'inputOptions' => [
                        'class' => 'form-control rounded-sm',
                        'placeholder' => 'Password',
                    ],
                ])->passwordInput()->label(false) ?>

                <?php if (!empty($model->isFirstLogin)): ?>
                    <div class="alert alert-info">Please set your new password below.</div>
                    <?= $form->field($model, 'new_password', [
                        'inputOptions' => [
                            'class' => 'form-control rounded-sm',
                            'placeholder' => 'New Password',
                        ],
                    ])->passwordInput()->label(false) ?>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center">
                    <?= $form->field($model, 'rememberMe', ['options' => ['class' => 'mb-0 mt-2']])->checkbox([
                        'class' => 'form-check-input',
                        'labelOptions' => ['class' => 'form-check-label ms-2'],
                    ]) ?>
                    <?= Html::a('Forgot Password?', ['site/request-password-reset'], ['class' => 'text-muted text-decoration-none small']) ?>
                </div>

                <div class="text-center mt-4">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary rounded-sm w-100 py-2']) ?>
                </div>

                <div class="text-center mt-3">
                    <?= Html::a('Super Admin Registration', ['site/super-admin-signup'], ['class' => 'btn btn-outline-primary rounded-sm w-100 py-2']) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger mt-3">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-login {
        background: linear-gradient(135deg, #1C1C4E, #E85720);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: -70px;
    }
    .card {
        border-radius: 0px;
    }
    .form-control {
        height: 45px;
        font-size: 16px;
        padding-left: 15px;
    }
    .btn-primary {
        background-color: #E85720;
        border-color: #E85720;
        transition: 0.3s;
    }
    .btn-primary:hover {
        background-color: #d04d1c;
        border-color: #d04d1c;
    }
    .btn-outline-primary {
        border-color: #E85720;
        color: #E85720;
    }
    .btn-outline-primary:hover {
        background-color: white;
        color: #1C1C4E;
    }
    .text-muted:hover {
        color: #E85720 !important;
    }
    .logo {
        max-height: 70px;
    }
CSS;
$this->registerCss($css);
?>