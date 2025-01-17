<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Json;
use app\models\Ticket;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $companyModules array */

$this->title = 'Create Ticket';

Yii::debug('ModuleIssues passed to view: ' . print_r($moduleIssues, true));
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Display flash messages -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'company_name')->textInput([
        'value' => Yii::$app->user->identity->company_name,
        'readonly' => true
    ]) ?>

    <?= $form->field($model, 'module')->dropDownList(
        $companyModules,
        [
            'prompt' => 'Select Module',
            'id' => 'ticket-module',
            'name' => 'Ticket[module]',
            'class' => 'form-control'
        ]
    ) ?>

    <?= $form->field($model, 'issue')->dropDownList(
        [],
        [
            'prompt' => 'Select Issue',
            'id' => 'ticket-issue',
            'name' => 'Ticket[issue]',
            'class' => 'form-control'
        ]
    )->hint('Please select a module first') ?>

    <?= $form->field($model, 'severity')->dropDownList(
        [
            Ticket::SEVERITY_LOW => 'Low',
            Ticket::SEVERITY_MEDIUM => 'Medium',
            Ticket::SEVERITY_HIGH => 'High',
            Ticket::SEVERITY_CRITICAL => 'Critical'
        ],
        [
            'prompt' => 'Select Severity',
            'name' => 'Ticket[severity]'
        ]
    ) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'screenshot')->fileInput([
        'class' => 'form-control',
        'accept' => 'image/*',
        'required' => true,
        'onchange' => 'validateFile(this)'
    ])->hint('Required. Allowed file types: PNG, JPG, JPEG, GIF. Max size: 2MB') ?>

    <div class="form-group">
        <?= Html::submitButton('Create Ticket', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$moduleIssuesJson = Json::encode($moduleIssues);
$script = <<<JS
$(document).ready(function() {
    console.log('Form initialized');
    var moduleIssues = {$moduleIssuesJson};
    console.log('Available module issues:', moduleIssues);

    $('#ticket-module').on('change', function() {
        var selectedModule = $(this).val();
        console.log('Module selected:', selectedModule);
        
        var issueDropdown = $('#ticket-issue');
        issueDropdown.empty().append($('<option>').text('Select Issue').val(''));
        
        if (selectedModule && moduleIssues[selectedModule]) {
            console.log('Loading issues for module:', selectedModule);
            issueDropdown.prop('disabled', false);
            moduleIssues[selectedModule].forEach(function(issue) {
                issueDropdown.append($('<option>').text(issue).val(issue));
            });
            console.log('Issues loaded:', moduleIssues[selectedModule]);
        } else {
            console.log('No issues available for module:', selectedModule);
            issueDropdown.prop('disabled', true);
        }
    });

    $('form').on('beforeSubmit', function(e) {
        console.log('Form submission started');
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);

        // Log each form field value
        console.group('Form Field Values');
        console.log('Company Name:', $('input[name="Ticket[company_name]"]').val());
        console.log('Module:', $('#ticket-module').val());
        console.log('Issue:', $('#ticket-issue').val());
        console.log('Severity:', $('select[name="Ticket[severity]"]').val());
        console.log('Description:', $('textarea[name="Ticket[description]"]').val());
        console.log('Screenshot:', $('input[name="Ticket[screenshot]"]')[0].files[0]?.name || 'No file');
        console.groupEnd();

        // Collect all form data
        var ticketData = {
            company_name: $('input[name="Ticket[company_name]"]').val(),
            module: $('#ticket-module').val(),
            issue: $('#ticket-issue').val(),
            severity: $('select[name="Ticket[severity]"]').val(),
            description: $('textarea[name="Ticket[description]"]').val(),
            screenshot: $('input[name="Ticket[screenshot]"]')[0].files[0]?.name || 'No file'
        };

        console.group('Ticket Data to be Saved');
        console.table(ticketData);
        console.groupEnd();

        // Show the data in an alert before proceeding
        Swal.fire({
            title: 'Data to be saved',
            html: '<pre>' + JSON.stringify(ticketData, null, 2) + '</pre>',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Proceed with save',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('User confirmed data submission');
                submitForm(form, formData, ticketData);
            } else {
                console.log('User cancelled submission');
            }
        });

        return false;
    });

    function submitForm(form, formData, ticketData) {
        console.group('Form Submission Process');
        console.log('Starting form submission');
        console.log('Form data:', ticketData);

        // Show loading state
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we create your ticket',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server Response Received:', response);
                console.table(response);
                
                if (response.success && response.ticket_id) {
                    console.log('Ticket created successfully:', response.ticket_id);
                    // Show success alert
                    Swal.fire({
                        icon: 'success',
                        title: response.title,
                        text: response.text,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log('Redirecting to:', response.redirectUrl);
                            window.location.href = response.redirectUrl;
                        }
                    });
                } else {
                    console.error('Error in response:', response);
                    // Show error alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred while creating the ticket'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.group('Ajax Error Details');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                console.groupEnd();

                // Show general error alert
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request'
                });
            },
            complete: function() {
                console.groupEnd(); // End Form Submission Process group
            }
        });
    }
});

// Log file validation
function validateFile(input) {
    console.group('File Validation');
    const file = input.files[0];
    
    if (!file) {
        console.log('No file selected');
        console.groupEnd();
        return true;
    }

    console.log('File details:', {
        name: file.name,
        type: file.type,
        size: file.size + ' bytes (' + (file.size/1024/1024).toFixed(2) + 'MB)'
    });

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!allowedTypes.includes(file.type)) {
        console.error('Invalid file type:', file.type);
        alert('Only JPG, PNG and GIF files are allowed');
        input.value = '';
        console.groupEnd();
        return false;
    }

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        console.error('File too large:', file.size);
        alert('File size must not exceed 5MB');
        input.value = '';
        console.groupEnd();
        return false;
    }

    console.log('File validation passed');
    console.groupEnd();
    return true;
}
JS;

$this->registerJs($script);
?>

<?php
// Make sure you have SweetAlert2 registered in your assets
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_HEAD]);
?>
