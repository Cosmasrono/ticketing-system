<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SignupForm */
/* @var $form ActiveForm */

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to signup:</p>

    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

        <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'company_email')->textInput(['readonly' => true]) ?>

        <?= $form->field($model, 'company_name')->textInput() ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'role')->textInput(['readonly' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton('Signup', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<< JS
$(document).ready(function() {
    $('#signupform-role').change(function() {
        var selectedRole = $(this).val();
        if (selectedRole === 'client') {
            $('#signupform-company_name').parent().show();
        } else {
            $('#signupform-company_name').parent().hide();
        }
    });
});
JS;
$this->registerJs($script);
?>

<style>
/* Orange-themed Signup Form Styles */
.site-signup {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #FFF3E0; /* Light orange background */
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.site-signup h1 {
    color: #FF9800; /* Orange */
    text-align: center;
    margin-bottom: 20px;
}

.site-signup p {
    color: #F57C00; /* Dark orange */
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border-color: #FFB74D; /* Light orange */
    background-color: #FFFFFF;
}

.form-control:focus {
    border-color: #FF9800; /* Orange */
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
}

.btn-primary {
    background-color: #FF9800; /* Orange */
    border-color: #FF9800;
}

.btn-primary:hover {
    background-color: #F57C00; /* Dark orange */
    border-color: #F57C00;
}

.help-block {
    color: #D84315; /* Deep orange for error messages */
}
</style>
