<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

// echo "CSRF Parameter: " . Yii::$app->request->csrfParam . "<br>";
// echo "CSRF Token: " . Yii::$app->request->csrfToken . "<br>";

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'options' => ['class' => 'form-vertical'],
                    ]); ?>

                    <?= $form->field($model, 'company_email', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Enter your company email',
                        'class' => 'form-control'
                    ]) ?>

                    <?= $form->field($model, 'password', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->passwordInput([
                        'placeholder' => 'Enter your password',
                        'class' => 'form-control'
                    ]) ?>

                    <?php if ($model->isFirstLogin): ?>
                        <div class="alert alert-info">
                            Please set your new password below.
                        </div>

                        <?= $form->field($model, 'new_password', [
                            'options' => ['class' => 'form-group mb-3']
                        ])->passwordInput([
                            'placeholder' => 'Enter new password',
                            'class' => 'form-control'
                        ]) ?>

                        <?= $form->field($model, 'confirm_password', [
                            'options' => ['class' => 'form-group mb-3']
                        ])->passwordInput([
                            'placeholder' => 'Confirm new password',
                            'class' => 'form-control'
                        ]) ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'rememberMe', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->checkbox([
                        'template' => "<div class=\"custom-control custom-checkbox\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
                        'class' => 'custom-control-input',
                        'labelOptions' => ['class' => 'custom-control-label'],
                    ]) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Login', [
                            'class' => 'btn btn-primary btn-block',
                            'name' => 'login-button'
                        ]) ?>
                    </div>

                    <div class="text-center mt-3">
                        <?= Html::a('Forgot Password?', ['site/request-password-reset'], [
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

    
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-login {
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
