<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $companyModules array */

$this->title = 'Create Ticket';

Yii::debug('ModuleIssues passed to view: ' . print_r($moduleIssues, true));
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

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
            'class' => 'form-control'
        ]
    ) ?>

    <?= $form->field($model, 'issue')->dropDownList(
        [],
        [
            'prompt' => 'Select Issue',
            'id' => 'ticket-issue',
            'class' => 'form-control'
        ]
    )->hint('Please select a module first') ?>


<?= $form->field($model, 'severity')->dropDownList(    // Changed from severity_level to severity
    [
        1 => 'Low',
        2 => 'Medium',
        3 => 'High',
        4 => 'Critical'
    ],
    ['prompt' => 'Select Severity']
) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'screenshot')->fileInput([
        'class' => 'form-control',
        'accept' => 'image/*'
    ])->hint('Allowed file types: PNG, JPG, JPEG. Max size: 2MB') ?>

    <div class="form-group">
        <?= Html::submitButton('Create Ticket', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$moduleIssuesJson = Json::encode($moduleIssues);
$script = <<<JS
    // Define moduleIssues globally
    var moduleIssues = {$moduleIssuesJson};
    
    $(document).ready(function() {
        // Module change handler
        $('#ticket-module').on('change', function() {
            var selectedModule = $(this).val();
            var issueDropdown = $('#ticket-issue');
            
            // Clear and disable issue dropdown
            issueDropdown.empty().append($('<option>').text('Select Issue').val(''));
            
            if (selectedModule && moduleIssues[selectedModule]) {
                // Enable and populate issues dropdown
                issueDropdown.prop('disabled', false);
                
                moduleIssues[selectedModule].forEach(function(issue) {
                    issueDropdown.append(
                        $('<option>')
                            .text(issue)
                            .val(issue)
                    );
                });
                
                // Trigger form validation if needed
                issueDropdown.trigger('change');
            } else {
                issueDropdown.prop('disabled', true);
            }
        });

        // Issue change handler
        $('#ticket-issue').on('change', function() {
            var selectedIssue = $(this).val();
            console.log('Issue Selection:', {
                selectedIssue: selectedIssue,
                dropdownEnabled: !$(this).prop('disabled'),
                availableOptions: $(this).find('option').map(function() {
                    return $(this).val();
                }).get()
            });
        });

        // Form submission handler
        $('form').on('beforeSubmit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(this);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Redirect silently without showing the JSON
                        window.location.href = response.redirectUrl;
                    } else {
                        // Show error message if needed
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'An error occurred'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing your request'
                    });
                }
            });
            
            return false;
        });

        // Initialize dropdowns on page load
        console.log('Initial State:', {
            moduleSelected: $('#ticket-module').val(),
            issueDropdownState: {
                disabled: $('#ticket-issue').prop('disabled'),
                value: $('#ticket-issue').val(),
                options: $('#ticket-issue option').length
            }
        });

        // Trigger module change if there's a pre-selected value
        if ($('#ticket-module').val()) {
            $('#ticket-module').trigger('change');
        }
    });
JS;

$this->registerJs($script);
?>