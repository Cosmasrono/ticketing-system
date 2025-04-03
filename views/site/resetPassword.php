<?php


use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Set Your Password';
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
                <p class="text-center text-muted">Please choose your new password:</p>

                <div class="row">
                    <div class="col-lg-12">
                        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                        <?= $form->field($model, 'password', [
                            'inputOptions' => [
                                'class' => 'form-control rounded-sm',
                                'placeholder' => 'New Password',
                                'autofocus' => true,
                            ],
                        ])->passwordInput()->label(false) ?>

                        <?= $form->field($model, 'confirmPassword', [
                            'inputOptions' => [
                                'class' => 'form-control rounded-sm',
                                'placeholder' => 'Confirm Password',
                            ],
                        ])->passwordInput()->label(false) ?>

                        <div class="text-center mt-4">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-primary rounded-sm w-100 py-2']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
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