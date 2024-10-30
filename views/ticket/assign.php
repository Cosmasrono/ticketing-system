<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $ticket app\models\Ticket */
/* @var $developers array */

$this->title = 'Assign Developer to Ticket: ' . $ticket->id;
?>

<div class="ticket-assign">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="ticket-form">
        <?php $form = ActiveForm::begin([
            'id' => 'assign-form',
            'options' => ['class' => 'form-horizontal'],
        ]); ?>

        <?= $form->field($ticket, 'assigned_to')->dropDownList(
            ArrayHelper::map($developers, 'id', 'name'),
            ['prompt' => 'Select Developer']
        ) ?>

        <div class="form-group">
            <?= Html::submitButton('Assign', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
