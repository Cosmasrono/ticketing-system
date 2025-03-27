<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Set Your Password';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-reset-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                <?= $form->field($model, 'current_password')->passwordInput(['placeholder' => 'Enter temporary password'])->label('Temporary Password') ?>

                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Enter new password'])->label('New Password') ?>

                <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => 'Confirm new password']) ?>

                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
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