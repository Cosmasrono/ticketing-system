<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Super Admin Registration';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup d-flex align-items-center justify-content-center">
    <div class="col-lg-10 col-md-10 col-sm-12 mx-auto">
        <div class="card shadow-lg border-0 rounded-0">
            <div class="card-body p-4">
                <div class="text-center mb-2">
                    <a href="<?= Yii::$app->homeUrl ?>">
                        <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Reset Password" class="img-fluid" style="max-width: 100px;">
                    </a>
                </div>
                <h2 class="text-center mb-3 fw-semibold">
                    <?= Html::encode($this->title) ?>
                </h2>

                <div class="alert alert-info text-center">
                    This registration is only for authorized super administrators.
                </div>

                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="alert alert-success">
                        <?= Yii::$app->session->getFlash('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <?php $form = ActiveForm::begin(['id' => 'form-signup', 'options' => ['class' => 'needs-validation', 'novalidate' => 'true']]); ?>

                <?= $form->field($model, 'name', [
                    'inputOptions' => ['class' => 'form-control rounded-sm', 'placeholder' => 'Full Name']
                ])->label(false) ?>

                <?= $form->field($model, 'company_name', [
                    'inputOptions' => ['class' => 'form-control rounded-sm', 'placeholder' => 'Company Name']
                ])->label(false) ?>

                <?= $form->field($model, 'company_email', [
                    'inputOptions' => ['class' => 'form-control rounded-sm', 'placeholder' => 'Company Email']
                ])->label(false) ?>

                <?= $form->field($model, 'password', [
                    'inputOptions' => ['class' => 'form-control rounded-sm', 'placeholder' => 'Password']
                ])->passwordInput()->label(false) ?>

                <?= $form->field($model, 'company_type')->hiddenInput(['value' => 'Admin'])->label(false) ?>
                <?= $form->field($model, 'subscription_level')->hiddenInput(['value' => 'Enterprise'])->label(false) ?>

                <div class="text-center mt-4">
                    <?= Html::submitButton('Register', ['class' => 'btn btn-primary rounded-sm w-100 py-2']) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <div class="text-center mt-3">
                    <p>Already have an account? <?= Html::a('Login here', ['site/login'], ['class' => 'text-muted text-decoration-none ms-2']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-signup {
        background: linear-gradient(135deg, #E85720, #1C1C4E);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        margin-top: -72px;
    }
    .col-lg-10 {
        max-width: 800px; /* Set max width */
    }
    .card {
        width: 100%; /* Make sure card fills the col-lg-10 */
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
    .text-muted:hover {
        color: #E85720 !important;
    }
    .logo {
        max-height: 70px;
    }
CSS;
$this->registerCss($css);
?>