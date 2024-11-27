<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Change Password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-change-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'change-password-form']); ?>

                <?= $form->field($model, 'old_password')->passwordInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'new_password')->passwordInput() ?>

                <?= $form->field($model, 'confirm_password')->passwordInput() ?>

                <div class="form-group">
                    <?= Html::submitButton('Change Password', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div> 