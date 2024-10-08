<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'company_email')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'rememberMe')->checkbox() ?>

        <div class="form-group">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

    <div class="form-group">
        <p>If you are a new user, you can <?= Html::a('signup here', ['site/signup']) ?>.</p>
    </div>

    <?= Html::a('Forgot password?', ['site/request-password-reset']) ?>
</div>

<style>

/* Orange-themed Login Form Styles */
.site-login {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #FFF3E0;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.site-login h1 {
    color: #FF9800;
    text-align: center;
    margin-bottom: 20px;
}

.site-login p {
    color: #F57C00;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border-color: #FFB74D;
}

.form-control:focus {
    border-color: #FF9800;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
}

.btn-primary {
    background-color: #FF9800;
    border-color: #FF9800;
}

.btn-primary:hover, .btn-primary:focus {
    background-color: #F57C00;
    border-color: #F57C00;
}

.checkbox label {
    color: #E65100;
}

a {
    color: #FF5722;
}

a:hover {
    color: #E64A19;
}