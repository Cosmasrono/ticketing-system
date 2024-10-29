<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $developers array */

$this->title = 'Assign Developer to Ticket: ' . $model->id;
?>
<div class="ticket-assign">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'assigned_to')->dropDownList($developers, ['prompt' => 'Select Developer']) ?>

    <div class="form-group">
        <?= Html::submitButton('Assign Developer', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['site/admin'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
