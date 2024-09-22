<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <p class="text-center">Please fill out the following fields to login:</p>

    <div class="row justify-content-center">
        <div class="col-lg-5">

        <?php $form = ActiveForm::begin([
            'action' => ['site/login'],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'text-danger'],
            ],
        ]); ?>

            <!-- Company Email -->
            <?= $form->field($model, 'company_email')->textInput([
                'autofocus' => true,
                'placeholder' => 'Enter your company email',
            ])->label('Company Email') ?>

            <!-- Password -->
            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Enter your password',
            ])->label('Password') ?>

            <!-- Remember Me Checkbox -->
            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<div class=\"form-check\">{input} {label}</div>\n<div>{error}</div>",
                'labelOptions' => ['class' => 'form-check-label'],
            ]) ?>

            <!-- Submit Button -->
            <div class="form-group text-center">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
        <div class="text-center">
            <p>Don't have an account? <?= Html::a('Sign Up', ['site/signup'], ['class' => 'link-primary']) ?></p>
        </div>

        </div>
    </div>
</div>
