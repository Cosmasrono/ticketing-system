<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SetPasswordForm */

$this->title = 'Set Your Password';
?>
<h1><?= Html::encode($this->title) ?></h1>

<div class="set-password-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'company_email')->textInput(['readonly' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= $form->field($model, 'confirmPassword')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Set Password', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
