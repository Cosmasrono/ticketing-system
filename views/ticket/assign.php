<?php
use app\models\Ticket;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="assign-form">
    <?php $form = ActiveForm::begin([
        'id' => 'assign-form',
        'enableAjaxValidation' => false,
    ]); ?>

    <?= $form->field($ticket, 'assigned_to')->dropDownList(
        \yii\helpers\ArrayHelper::map(
            User::find()
                ->select(['id', 'name'])
                ->where(['role' => User::ROLE_DEVELOPER])
                ->andWhere(['status' => User::STATUS_ACTIVE])
                ->orderBy(['name' => SORT_ASC])
                ->all(), 
            'id', 
            'name'
        ),
        [
            'prompt' => 'Select Developer',
            'class' => 'form-control',
            'required' => true
        ]
    ) ?>

    <?php if ($ticket->status === Ticket::STATUS_ESCALATED): ?>
        <?= Html::hiddenInput('isReassignment', '1') ?>
        <div class="alert alert-info">
            This ticket will be reassigned to the selected developer.
        </div>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton(
            $ticket->status === Ticket::STATUS_ESCALATED ? 'Reassign' : 'Assign', 
            ['class' => 'btn btn-primary']
        ) ?>
        <?= Html::button('Cancel', [
            'class' => 'btn btn-secondary',
            'data-bs-dismiss' => 'modal'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
$('#assign-form').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this);
    var developerId = $('#ticket-assigned_to').val();
    
    if (!developerId) {
        alert('Please select a developer');
        return false;
    }

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        beforeSend: function() {
            $('.btn-primary').prop('disabled', true).text('Processing...');
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Close modal if it exists
                        if ($('#assignModal').length) {
                            $('#assignModal').modal('hide');
                        }
                        // Redirect to admin page
                        window.location.href = '/site/admin';  // Update this URL to match your admin route
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to assign developer',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $('.btn-primary').prop('disabled', false)
                    .text('{$ticket->status}' === 'escalated' ? 'Reassign' : 'Assign');
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while processing your request',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            $('.btn-primary').prop('disabled', false)
                .text('{$ticket->status}' === 'escalated' ? 'Reassign' : 'Assign');
        }
    });
    
    return false;
});
JS;

$this->registerJs($js);
?>