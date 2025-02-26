<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request Password Reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset d-flex align-items-center justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-lg border-0 rounded-0">
            <div class="card-body p-5 text-center">
                <div class="text-center mb-2">
                    <a href="<?= Yii::$app->homeUrl ?>">
                        <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Reset Password" class="img-fluid" style="max-width: 100px;">
                    </a>
                </div>
                <h2 class="text-center mb-4"><?= Html::encode($this->title) ?></h2>

                <p class="text-muted text-center mb-2">
                    Please enter your email. A link to reset your password will be sent there.
                </p>

                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="alert alert-success">
                        <?= Yii::$app->session->getFlash('success') ?>
                    </div>
                <?php elseif (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php else: ?>
                    <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

                    <?= $form->field($model, 'company_email', [
                        'options' => ['class' => 'text-left form-group mb-3']
                    ])->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Enter your company email',
                        'class' => 'form-control rounded-sm'
                    ])->label(false) ?>

                    <div class="text-center mt-4">
                        <?= Html::submitButton('Send Reset Link', ['class' => 'btn btn-primary rounded-sm w-100 py-2']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-request-password-reset {
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
CSS;
$this->registerCss($css);
?>