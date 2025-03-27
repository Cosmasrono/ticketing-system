<?php
use app\models\Ticket;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* Container Styling */
    .container {
        max-width: 100%;
        padding: 0px;
        /* Allow full width */
        /* Add padding for mobile */
    }

    .finefooter {
        padding: 0px 60px;
        margin-bottom: -20px;

    }
</style>
<style>
.assign-form {
    padding: 25px;
    border-radius: 8px;
    background: #ffffff;  /* Light orange background */
 
}

.form-control {
    border-color: #ff9966;
    background-color: #fff;
}

.form-control:focus {
    border-color: #ff7733;
    box-shadow: 0 0 0 0.2rem rgba(255, 153, 102, 0.25);
}

.btn-primary {
    background-color: #ff7733 !important;  /* Orange primary button */
    border-color: #ff6600 !important;
    color: white !important;
}

.btn-primary:hover {
    background-color: #ff6600 !important;
    border-color: #ff5500 !important;
}

.btn-primary:disabled {
    background-color: #ffaa80 !important;
    border-color: #ff9966 !important;
}

.btn-secondary {
    background-color: #f8f9fa;
    border-color: #ff9966;
    color: #ff7733;
}

.btn-secondary:hover {
    background-color: #ffe6d9;
    border-color: #ff7733;
    color: #ff6600;
}

.alert-info {
    background-color: #fff3e6;
    border-color: #ffd9b3;
    color: #cc5200;
}

.form-label {
    color: #cc5200;
    font-weight: 600;
}

select.form-control {
    border-radius: 6px;
    padding: 10px;
    border-color: #ffd3b6;
}

select.form-control:focus {
    border-color: #ff7733;
}

.form-group {
    margin-bottom: 1.5rem;
}

.mt-3 {
    margin-top: 1.5rem !important;
}

/* Style for the dropdown arrow */
select.form-control {
    background-image: linear-gradient(45deg, transparent 50%, #ff7733 50%), 
                      linear-gradient(135deg, #ff7733 50%, transparent 50%);
    background-position: calc(100% - 20px) calc(1em + 2px),
                         calc(100% - 15px) calc(1em + 2px);
    background-size: 5px 5px,
                    5px 5px;
    background-repeat: no-repeat;
    -webkit-appearance: none;
    -moz-appearance: none;
}
</style>
<div class="row col-lg-6 offset-lg-3 " style="margin-top:30px;">
<div class="assign-form" style="">
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
            'class' => 'form-control ',
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
        </div>
<?php
$js = <<<JS
$('#assign-form').off('submit').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this);
    var developerId = $('#ticket-assigned_to').val();
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    
    if (!developerId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a developer',
            icon: 'error'
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
        timeout: 30000,
        headers: {
            'X-CSRF-Token': csrfToken
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
            console.error('AJAX Error:', status, error);
            let errorMessage = 'An error occurred while processing your request';
            
            if (status === 'timeout') {
                errorMessage = 'Request timed out. Please try again.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error'
            });
        },
        complete: function() {
            // Optional: Add any cleanup code here
        }
    });
    
    return false;
});
JS;

$this->registerJs($js);
?>