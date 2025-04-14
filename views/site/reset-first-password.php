<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Reset Password';
?>

<div class="site-reset-password">
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="card-body">
                    <p>Please change your temporary password:</p>

                    <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                    <?= $form->field($model, 'current_password')
                        ->passwordInput(['placeholder' => 'Enter your temporary password']) ?>

                    <?= $form->field($model, 'new_password')
                        ->passwordInput(['placeholder' => 'Enter new password']) ?>

                    <?= $form->field($model, 'confirm_password')
                        ->passwordInput(['placeholder' => 'Confirm new password']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Change Password', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card {
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.card-header {
    background-color: #f8f9fa;
    padding: 15px;
}
.card-body {
    padding: 20px;
}
.help-block {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
}
</style> 