<?php
use app\models\Ticket;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="assign-form">
    <?php $form = ActiveForm::begin([
        'id' => 'assign-form',
        'enableAjaxValidation' => false,
    ]); ?>

    <?= $form->field($ticket, 'assigned_to')->dropDownList(
        \yii\helpers\ArrayHelper::map($developers, 'id', 'name'),
        ['prompt' => 'Select Developer']
    ) ?>

    <?php if ($ticket->status === Ticket::STATUS_ESCALATED): ?>
        <?= Html::hiddenInput('isReassignment', '1') ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Assign', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('Cancel', [
            'class' => 'btn btn-secondary',
            'data-dismiss' => 'modal'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>