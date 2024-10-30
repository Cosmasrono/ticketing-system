<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $moduleIssues array */
/* @var $currentIssues array */

$this->title = 'Create Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'module')->textInput(['readonly' => true]) ?>

    <?= $form->field($model, 'issue')->dropDownList(
        array_combine($currentIssues, $currentIssues),
        ['prompt' => 'Select an Issue']
    ) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'screenshot')->fileInput(['accept' => 'image/*']) ?>
    <?= $form->field($model, 'screenshot_base64')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Create Ticket', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs("
    document.querySelector('input[type=\"file\"]').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onloadend = function() {
                document.getElementById('ticket-screenshot_base64').value = reader.result.split(',')[1];
            }
            reader.readAsDataURL(file);
        }
    });
");
?>
