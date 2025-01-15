<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Set Your Password';
?>

<div class="site-set-password">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Please set your new password:</p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'set-password-form',
                        'options' => [
                            'autocomplete' => 'off',
                            'class' => 'needs-validation'
                        ],
                        'enableAjaxValidation' => false,
                        'enableClientValidation' => true,
                    ]); ?>

                    <?= $form->field($model, 'new_password', [
                        'options' => ['class' => 'form-group mb-3'],
                        'template' => "{label}\n{input}\n{hint}\n{error}"
                    ])->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your new password',
                        'autocomplete' => 'new-password'
                    ])->hint('Password must be at least 6 characters long') ?>

                    <?= $form->field($model, 'confirm_password', [
                        'options' => ['class' => 'form-group mb-4'],
                        'template' => "{label}\n{input}\n{hint}\n{error}"
                    ])->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Confirm your new password',
                        'autocomplete' => 'new-password'
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Set Password', [
                            'class' => 'btn btn-primary btn-block w-100',
                            'name' => 'set-password-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.site-set-password {
    padding: 20px;
}
.card {
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.card-header {
    background-color: #f8f9fa;
    padding: 15px;
}
.btn-block {
    width: 100%;
}
</style>