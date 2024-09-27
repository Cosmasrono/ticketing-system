<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $ticket app\models\Ticket */
/* @var $developers app\models\Developer[] */
/* @var $isAssigned boolean */

$this->title = $isAssigned ? 'Reassign Ticket #' . $ticket->id : 'Assign Ticket #' . $ticket->id;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $ticket->id, 'url' => ['view', 'id' => $ticket->id]];
$this->params['breadcrumbs'][] = $isAssigned ? 'Reassign' : 'Assign';
?>
<div class="ticket-assign">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($ticket, 'assigned_to')->dropDownList(
        \yii\helpers\ArrayHelper::map($developers, 'id', 'name'),
        ['prompt' => 'Select Developer']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton($isAssigned ? 'Reassign Developer' : 'Assign Developer', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>