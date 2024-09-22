<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Developer;


/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $developers app\models\Developer[] */

$this->title = 'Assign Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-assign">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($model->assigned_to): ?>
        <div class="alert alert-info">
            This ticket is currently assigned to: <?= Html::encode(Developer::findOne($model->assigned_to)->name) ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'assigned_to')->dropDownList(
        \yii\helpers\ArrayHelper::map($developers, 'id', 'name'),
        ['prompt' => 'Select Developer']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton('Assign', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>