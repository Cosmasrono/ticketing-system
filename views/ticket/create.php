<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Create Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get the current user's selected modules
$userModules = array_map('trim', explode(',', Yii::$app->user->identity->selectedModules));
$modulesList = array_combine($userModules, $userModules);
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <?= $form->field($model, 'selectedModule')->dropDownList(
                $modulesList,  // Use the modules assigned to the user
                [
                    'prompt' => 'Select a module',
                    'class' => 'form-control'
                ]
            ) ?>

            <?= $form->field($model, 'issue')->dropDownList(
                [],
                [
                    'prompt' => 'Select an issue',
                    'id' => 'ticket-issue',
                    'class' => 'form-control'
                ]
            ) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

            <?= $form->field($model, 'screenshot')->fileInput([
                'accept' => 'image/*',
                'class' => 'form-control'
            ]) ?>
            
            <?= $form->field($model, 'screenshot_base64')->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton('Create Ticket', [
                    'class' => 'btn btn-success',
                    'data-loading-text' => 'Creating...'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <?php if (!empty($recentTickets)): ?>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Recent Tickets</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <?php foreach ($recentTickets as $ticket): ?>
                            <li>
                                <?= Html::a(
                                    "#" . $ticket->id . " - " . $ticket->module,
                                    ['view', 'id' => $ticket->id]
                                ) ?>
                                <small class="text-muted">
                                    (<?= Yii::$app->formatter->asRelativeTime($ticket->created_at) ?>)
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
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
    $('input[type="file"]').change(function() {
        var file = this.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must not exceed 2MB');
                this.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onloadend = function() {
                $('#ticket-screenshot_base64').val(reader.result.split(',')[1]);
            }
            reader.readAsDataURL(file);
        }
    });
});
JS;
$this->registerJs($script);
?>