<?php
use app\models\Ticket;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $ticket app\models\Ticket */
/* @var $developers array */

$this->title = 'Assign Ticket #' . $ticket->id;
?>

<div class="ticket-assign">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'assign-form',
        'enableAjaxValidation' => false,
        'options' => ['class' => 'assign-ticket-form']
    ]); ?>

    <div class="form-group">
        <?= $form->field($ticket, 'assigned_to')->dropDownList(
            $developers,
            [
                'prompt' => 'Select Developer',
                'class' => 'form-control',
                'id' => 'ticket-assigned_to'
            ]
        ) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(
            $ticket->status === 'escalated' ? 'Reassign' : 'Assign',
            [
                'class' => 'btn btn-primary' . (empty($developers) ? ' disabled' : ''),
                'id' => 'assign-submit-btn'
            ]
        ) ?>
        <?= Html::a('Cancel', ['view', 'id' => $ticket->id], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
// Add debug logging
console.log('Form handler initialized');

$('#assign-form').on('submit', function(e) {
    e.preventDefault();
    console.log('Form submitted');
    
    var form = $(this);
    var developerId = $('#ticket-assigned_to').val();
    console.log('Selected developer ID:', developerId);
    
    // Show loading overlay
    Swal.fire({
        title: 'Processing...',
        text: 'Assigning ticket to developer',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    if (!developerId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a developer',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }

    // Show loading state
    Swal.fire({
        title: 'Processing...',
        text: 'Assigning ticket to developer',
        didOpen: () => {
            Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    });

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        beforeSend: function() {
            console.log('Sending AJAX request');
            $('.btn-primary').prop('disabled', true).text('Processing...');
        },
        success: function(response) {
            console.log('Response received:', response);
            
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Redirecting to admin page');
                        window.location.href = '/site/admin';
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to assign developer',
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while processing your request: ' + error,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        },
        complete: function() {
            // Optional: Add any cleanup code here
        }
    });
    
    return false;
});

// Add form change handler for immediate feedback
$('#ticket-assigned_to').on('change', function() {
    console.log('Developer selection changed:', $(this).val());
    if ($(this).val()) {
        $('.btn-primary').removeClass('disabled');
    } else {
        $('.btn-primary').addClass('disabled');
    }
});

// Debug check if scripts are loaded
$(document).ready(function() {
    console.log('Document ready, form ID found:', $('#assign-form').length > 0);
});
JS;

// Make sure SweetAlert2 is included
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJs($js, \yii\web\View::POS_READY);
?>