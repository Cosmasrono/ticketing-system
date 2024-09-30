<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Enter Reset Token';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-enter-reset-token">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please enter your password reset token:</p>

    <?php $form = ActiveForm::begin(['id' => 'enter-reset-token-form']); ?>

        <?= $form->field($model, 'token')->textInput(['autofocus' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>
