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

    
</div>

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
    border-color: #
</style>