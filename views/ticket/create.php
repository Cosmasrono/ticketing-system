<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Create Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get the current user's selected modules
$userModules = array_map('trim', explode(',', Yii::$app->user->identity->selectedModules));
$modulesList = array_combine($userModules, $userModules);
?>

<div class="site-ticket-create">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= Yii::$app->session->getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'ticket-form',
                        'options' => [
                            'class' => 'form-vertical',
                            'enctype' => 'multipart/form-data'
                        ],
                    ]); ?>

                    <?= $form->field($model, 'selectedModule', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->dropDownList(
                        $modulesList,
                        [
                            'prompt' => 'Select a module',
                            'class' => 'form-control form-select'
                        ]
                    ) ?>

                    <?= $form->field($model, 'issue', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->dropDownList(
                        [],
                        [
                            'prompt' => 'Select an issue',
                            'id' => 'ticket-issue',
                            'class' => 'form-control form-select'
                        ]
                    ) ?>

                    <?= $form->field($model, 'description', [
                        'options' => ['class' => 'form-group mb-3']
                    ])->textarea([
                        'rows' => 6,
                        'class' => 'form-control',
                        'placeholder' => 'Please provide detailed description of the issue...'
                    ]) ?>

                    <?= $form->field($model, 'screenshot')->fileInput([
                        'accept' => 'image/*',
                        'class' => 'form-control'
                    ])->hint('Max file size: 5MB. Allowed extensions: png, jpg, jpeg, gif') ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Create Ticket', [
                            'class' => 'btn btn-primary btn-block',
                            'name' => 'create-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success mt-3">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-ticket-create {
        padding: 40px 0;
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
        border-radius: 8px;
        border: none;
    }
    .card-body {
        padding: 30px;
    }
    .form-control, .form-select {
        height: 45px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    textarea.form-control {
        height: auto;
    }
    .btn-primary {
        height: 45px;
        font-size: 16px;
        padding: 0 30px;
        border-radius: 4px;
        width: 200px;
    }
    .alert {
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .form-group.mb-3 {
        margin-bottom: 1.5rem !important;
    }
    .text-muted {
        font-size: 0.875rem;
    }
CSS;
$this->registerCss($css);

// Add this at the beginning of your JavaScript to get the correct URL
$getIssuesUrl = \yii\helpers\Url::to(['ticket/get-issues']);

$script = <<<JS
$(document).ready(function() {
    $('#ticket-selectedmodule').change(function() {
        var selectedModule = $(this).val();
        var issueDropdown = $('#ticket-issue');
        
        console.log('Selected Module:', selectedModule); // Debug line
        
        if (selectedModule) {
            $.ajax({
                url: '{$getIssuesUrl}', // Use the correct URL
                type: 'POST',
                data: {
                    module: selectedModule,
                    _csrf: yii.getCsrfToken()
                },
                dataType: 'json',
                beforeSend: function() {
                    console.log('Sending request for module:', selectedModule); // Debug line
                    issueDropdown.html('<option>Loading...</option>');
                },
                success: function(response) {
                    console.log('Server Response:', response); // Debug line
                    
                    issueDropdown.empty();
                    issueDropdown.append($('<option>', {
                        value: '',
                        text: 'Select an Issue'
                    }));
                    
                    if (response.success && response.issues && response.issues.length > 0) {
                        $.each(response.issues, function(index, issue) {
                            issueDropdown.append($('<option>', {
                                value: issue,
                                text: issue
                            }));
                        });
                    } else {
                        console.log('No issues found for module:', selectedModule); // Debug line
                        issueDropdown.append($('<option>', {
                            value: '',
                            text: 'No issues available for this module'
                        }));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    issueDropdown.html('<option>Error loading issues</option>');
                }
            });
        } else {
            issueDropdown.html('<option value="">Select Module First</option>');
        }
    });

    // File upload handler
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must not exceed 5MB');
            this.value = ''; // Clear the file input
            return;
        }
    });

    // Remove or update the form submit handler since we're no longer using base64
    document.querySelector('form').addEventListener('submit', function(e) {
        // Add any validation if needed
    });
});
JS;
$this->registerJs($script);
?>