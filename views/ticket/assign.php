<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $ticket app\models\Ticket */
/* @var $developers app\models\User[] */

$this->title = 'Assign Ticket: ' . $ticket->title;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $ticket->title, 'url' => ['view', 'id' => $ticket->id]];
$this->params['breadcrumbs'][] = 'Assign';
?>
<div class="ticket-assign">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($ticket, 'assigned_to')->dropDownList(
        \yii\helpers\ArrayHelper::map($developers, 'id', 'username'),
        ['prompt' => 'Select Developer']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton('Assign', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
