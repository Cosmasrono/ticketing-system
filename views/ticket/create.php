<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Json;
use app\models\Ticket;

$this->title = 'Create Ticket';
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

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

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
            'id' => 'ticket-form'
        ]
    ]); ?>

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

    <?= $form->field($model, 'severity')->dropDownList(
        [
            Ticket::SEVERITY_LOW => 'Low',
            Ticket::SEVERITY_MEDIUM => 'Medium',
            Ticket::SEVERITY_HIGH => 'High',
            Ticket::SEVERITY_CRITICAL => 'Critical'
        ],
        [
            'prompt' => 'Select Severity'
        ]
    ) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'screenshot')->fileInput([
        'class' => 'form-control',
        'accept' => 'image/*',
        'required' => true
    ])->hint('Required. Allowed file types: PNG, JPG, JPEG, GIF. Max size: 5MB') ?>

    <!-- Image preview container -->
    <div class="preview-container mt-2 mb-3" style="display:none;">
        <img id="image-preview" src="" alt="Preview" style="max-width: 200px;" class="img-thumbnail">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Create Ticket', [
            'class' => 'btn btn-success',
            'id' => 'submit-button'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$moduleIssuesJson = Json::encode($moduleIssues);
$script = <<<JS
$(document).ready(function() {
    var moduleIssues = {$moduleIssuesJson};

    // Handle module change
    $('#ticket-module').on('change', function() {
        var selectedModule = $(this).val();
        var issueDropdown = $('#ticket-issue');
        issueDropdown.empty().append($('<option>').text('Select Issue').val(''));
        
        if (selectedModule && moduleIssues[selectedModule]) {
            issueDropdown.prop('disabled', false);
            moduleIssues[selectedModule].forEach(function(issue) {
                issueDropdown.append($('<option>').text(issue).val(issue));
            });
        } else {
            issueDropdown.prop('disabled', true);
        }
    });

    // Handle file input change for preview
    $('#ticket-screenshot').on('change', function() {
        const file = this.files[0];
        if (file && validateFile(this)) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('.preview-container').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('.preview-container').hide();
        }
    });

    // File validation
    function validateFile(input) {
        const file = input.files[0];
        if (!file) return false;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Please upload only JPG, PNG or GIF files'
            });
            input.value = '';
            return false;
        }

        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'File size must not exceed 5MB'
            });
            input.value = '';
            return false;
        }

        return true;
    }

    // Form submission
    $('#ticket-form').on('beforeSubmit', function(e) {
        e.preventDefault();
        
        if (!validateFile($('#ticket-screenshot')[0])) {
            return false;
        }

        // Show loading state
        Swal.fire({
            title: 'Submitting...',
            text: 'Please wait while we create your ticket',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit form normally
        return true;
    });
});
JS;

$this->registerJs($script);
?>