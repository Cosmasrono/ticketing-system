<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $developers app\models\Developer[] */
/* @var $isAssigned boolean */

$this->title = $isAssigned ? 'Reassign Ticket' : 'Assign Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-assign">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="ticket-form">
        <?php $form = ActiveForm::begin(['id' => 'assign-form']); ?>

        <?= $form->field($model, 'assigned_to')->dropDownList(
            \yii\helpers\ArrayHelper::map($developers, 'id', 'name'),
            ['prompt' => 'Select a developer']
        ) ?>

        <div class="form-group">
            <?= Html::submitButton($isAssigned ? 'Reassign' : 'Assign', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>

<?php
$this->registerJs("
    $('#assign-form').on('beforeSubmit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = response.redirectUrl;
                } else {
                    console.error('Error response:', response);
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                console.log('Response:', jqXHR.responseText);
                alert('Request failed. Please check the console for more information.');
            }
        });
        return false;
    });
");
?>