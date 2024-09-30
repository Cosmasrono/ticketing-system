<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to signup:</p>

    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

        <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'company_email') ?>

        <?= $form->field($model, 'company_name') ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'role')->dropDownList([
            User::ROLE_USER => 'User',
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_DEVELOPER => 'Developer',
        ], ['id' => 'role-dropdown']) ?>

        <div class="form-group">
            <?= Html::submitButton('Signup', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

    <p>
        Forgot your password? <?= Html::a('Reset it here', ['site/reset']) ?>
    </p>
</div>