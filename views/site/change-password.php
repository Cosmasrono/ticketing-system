<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Change Password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-change-password">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center mb-4">
                        Please set your new password for account: <strong><?= Html::encode($email) ?></strong>
                    </p>

                    <?php $form = ActiveForm::begin(['id' => 'change-password-form']); ?>

                    <?= $form->field($model, 'old_password')->passwordInput([
                        'autofocus' => true,
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Enter temporary password from email'
                    ]) ?>

                    <?= $form->field($model, 'new_password')->passwordInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Enter new password'
                    ]) ?>

                    <?= $form->field($model, 'confirm_password')->passwordInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Confirm new password'
                    ]) ?>

                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton('Change Password', [
                            'class' => 'btn btn-primary btn-lg px-5'
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
    .site-change-password {
        padding: 40px 0;
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: none;
    }
    .card-body {
        padding: 2rem;
    }
CSS;
$this->registerCss($css);
?> 