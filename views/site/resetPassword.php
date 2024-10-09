<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please choose your new password:</p>

    <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

        <?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password_repeat')->passwordInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
/* Orange-themed Reset Password Form Styles */
.site-reset-password {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #FFF3E0; /* Light orange background */
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.site-reset-password h1 {
    color: #FF9800; /* Orange */
    text-align: center;
    margin-bottom: 20px;
}

.site-reset-password p {
    color: #F57C00; /* Dark orange */
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border-color: #FFB74D; /* Light orange */
}

.form-control:focus {
    border-color: #FF9800; /* Orange */
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
}

.btn-primary {
    background-color: #FF9800; /* Orange */
    border-color: #FF9800;
}

.btn-primary:hover, .btn-primary:focus {
    background-color: #F57C00; /* Dark orange */
    border-color: #F57C00;
}

a {
    color: #FF5722; /* Red-orange */
}

a:hover {
    color: #E64A19; /* Dark red-orange */
}
</style>
