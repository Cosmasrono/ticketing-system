<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

$this->title = 'Create Ticket';
// $this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;

// Get the current user's selected modules
$userModules = array_map('trim', explode(',', Yii::$app->user->identity->selectedModules));
$modulesList = array_combine($userModules, $userModules);

$cloudinaryUploadUrl = Url::to(['ticket/upload-to-cloudinary']);
$getIssuesUrl = Url::to(['ticket/get-issues']);
?>

<style>
.site-ticket-create {
    padding: 20px 0;
}

.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.card-body {
    padding: 2rem;
}

.form-control, .form-select {
    border-radius: 8px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #ff9800;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
}

.btn-primary {
    background-color: #ff9800;
    border-color: #ff9800;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #e68900;
    border-color: #e68900;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-danger {
    border-radius: 6px;
    padding: 8px 16px;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #555;
}

.help-block {
    font-size: 0.875rem;
    color: #666;
    margin-top: 5px;
}

#image-preview-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}

#screenshot-preview {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.alert-danger {
    background-color: #fff3f3;
    color: #dc3545;
    border-left: 4px solid #dc3545;
}

h1 {
    color: #333;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.form-hint {
    font-size: 0.875rem;
    color: #666;
    margin-top: 5px;
}

/* Custom file input styling */
.form-control[type="file"] {
    padding: 8px;
    background-color: #f8f9fa;
}

.form-control[type="file"]::-webkit-file-upload-button {
    background-color: #ff9800;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    margin-right: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-control[type="file"]::-webkit-file-upload-button:hover {
    background-color: #e68900;
}

/* Remove image button styling */
#remove-image {
    margin-top: 10px;
    background-color: #dc3545;
    border: none;
    transition: all 0.3s ease;
}

#remove-image:hover {
    background-color: #c82333;
    transform: translateY(-1px);
}

/* Textarea styling */
textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

/* Dropdown hover effect */
.form-select:hover {
    border-color: #ff9800;
}
</style>

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
                        'class' => 'form-control',
                        'id' => 'ticket-screenshot'
                    ])->hint('Max file size: 5MB. Allowed extensions: png, jpg, jpeg, gif') ?>

                    <div class="form-group mb-3">
                        <div id="image-preview-container" style="display:none;">
                            <img id="screenshot-preview" class="img-thumbnail mb-2" style="max-width: 300px;" />
                            <button type="button" class="btn btn-sm btn-danger d-block" id="remove-image">Remove Image</button>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton('Create Ticket', [
                            'class' => 'btn btn-primary btn-lg',
                            'name' => 'create-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<<JS
$(document).ready(function() {
    $('#ticket-selectedmodule').change(function() {
        var selectedModule = $(this).val();
        var issueDropdown = $('#ticket-issue');
        
        if (selectedModule) {
            $.ajax({
                url: '{$getIssuesUrl}',
                type: 'POST',
                data: {
                    module: selectedModule,
                    _csrf: yii.getCsrfToken()
                },
                dataType: 'json',
                beforeSend: function() {
                    issueDropdown.html('<option value="">Loading...</option>');
                },
                success: function(response) {
                    issueDropdown.empty();
                    issueDropdown.append($('<option>', {
                        value: '',
                        text: 'Select an Issue'
                    }));
                    
                    if (response.success && response.issues) {
                        $.each(response.issues, function(index, issue) {
                            issueDropdown.append($('<option>', {
                                value: issue,
                                text: issue
                            }));
                        });
                    }
                },
                error: function() {
                    issueDropdown.html('<option value="">Error loading issues</option>');
                }
            });
        } else {
            issueDropdown.html('<option value="">Select Module First</option>');
        }
    });

    const fileInput = $('#ticket-screenshot');
    const previewContainer = $('#image-preview-container');
    const preview = $('#screenshot-preview');
    const removeButton = $('#remove-image');

    fileInput.on('change', async function(e) {
        const file = this.files[0];
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must not exceed 5MB');
            this.value = '';
            previewContainer.hide();
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_csrf', yii.getCsrfToken());

        try {
            const response = await fetch('{$cloudinaryUploadUrl}', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Network response was not ok');
            const result = await response.json();

            if (result.success) {
                preview.attr('src', result.url);
                previewContainer.show();
                
                // Update hidden input with Cloudinary URL
                let hiddenInput = $('input[name="Ticket[screenshotUrl]"]');
                if (hiddenInput.length === 0) {
                    hiddenInput = $('<input>').attr({
                        type: 'hidden',
                        name: 'Ticket[screenshotUrl]'
                    }).appendTo('#ticket-form');
                }
                hiddenInput.val(result.url);
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Upload failed: ' + error.message);
            this.value = '';
            previewContainer.hide();
        }
    });

    removeButton.on('click', function() {
        fileInput.val('');
        previewContainer.hide();
        $('input[name="Ticket[screenshotUrl]"]').remove();
    });
});
JS;

$this->registerJs($script, \yii\web\View::POS_READY);
?>