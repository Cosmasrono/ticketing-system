<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Set Your New Password';
?>

<div class="site-first-login">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center h3 mb-4"><?= Html::encode($this->title) ?></h1>
                    
                    <?php if (isset($user)): ?>
                        <div class="alert alert-info">
                            Welcome <?= Html::encode($user->name) ?>! Please set your new password.
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin(['id' => 'first-login-form']); ?>

                        <?= $form->field($model, 'current_password')->passwordInput([
                            'autofocus' => true,
                            'class' => 'form-control',
                            'placeholder' => 'Enter the temporary password from your email'
                        ]) ?>

                        <?= $form->field($model, 'new_password')->passwordInput([
                            'class' => 'form-control',
                            'placeholder' => 'Enter your new password'
                        ]) ?>

                        <?= $form->field($model, 'confirm_password')->passwordInput([
                            'class' => 'form-control',
                            'placeholder' => 'Confirm your new password'
                        ]) ?>

                        <div class="form-group text-center mt-4">
                            <?= Html::submitButton('Change Password', [
                                'class' => 'btn btn-primary btn-block',
                                'name' => 'change-button'
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
.site-first-login {
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
CSS;
$this->registerCss($css);
?> 