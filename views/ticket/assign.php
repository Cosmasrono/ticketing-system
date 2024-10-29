<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $developers array */

$this->title = 'Assign Developer to Ticket: ' . $model->id;
?>

<!-- message -->
<?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?> 
<div class="ticket-assign">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['id' => 'assign-form']); ?>

    <?= $form->field($model, 'assigned_to')->dropDownList($developers, [
        'prompt' => 'Select Developer'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Assign', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
