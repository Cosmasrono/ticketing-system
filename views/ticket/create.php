<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Create Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="ticket-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>

        <?php ActiveForm::end(); ?>

    </div>

</div>
