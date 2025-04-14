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

<div class="ticket-assign card shadow-sm p-4 mb-5">
    <h1 class="mb-4 text-primary"><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'assign-form',
        'enableAjaxValidation' => false,
        'options' => ['class' => 'assign-ticket-form']
    ]); ?>

    <div class="form-group mb-4">
        <?= $form->field($ticket, 'assigned_to')->dropDownList(
            $developers,
            [
                'prompt' => 'Select Developer',
                'class' => 'form-control form-select',
                'id' => 'ticket-assigned_to'
            ]
        )->label('Assign To Developer', ['class' => 'form-label fw-bold']) ?>
    </div>

    <div class="form-group d-flex gap-3 mt-4">
        <?= Html::submitButton(
            $ticket->status === 'escalated' ? 'Reassign' : 'Assign',
            [
                'class' => 'btn btn-primary' . (empty($developers) ? ' disabled' : ''),
                'id' => 'assign-submit-btn'
            ]
        ) ?>
        <?= Html::a('Cancel', ['view', 'id' => $ticket->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
// Styling for the form
$css = <<<CSS
.ticket-assign {
    max-width: 600px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
}

.ticket-assign h1 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 15px;
}

.form-select {
    height: 45px;
    border-radius: 6px;
    border: 1px solid #ced4da;
    box-shadow: none;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-label {
    font-size: 0.95rem;
    margin-bottom: 8px;
}

.btn-primary {
    background-color: #4e73df;
    border-color: #4e73df;
    padding: 10px 20px;
    font-weight: 500;
    letter-spacing: 0.3px;
    transition: all 0.2s;
}

.btn-primary:hover:not(.disabled) {
    background-color: #3a5ccc;
    border-color: #3a5ccc;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
    padding: 10px 20px;
    font-weight: 500;
    letter-spacing: 0.3px;
    transition: all 0.2s;
}

.btn-outline-secondary:hover {
    color: #fff;
    background-color: #6c757d;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

@media (max-width: 768px) {
    .ticket-assign {
        padding: 20px 15px;
    }
    
    .form-group.d-flex {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}
CSS;

$this->registerCss($css);

// Keep the original JavaScript as is
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