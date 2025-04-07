<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Json;
use app\models\Ticket;

$this->title = 'Create Ticket';

$moduleIssuesJson = Json::encode($moduleIssues);
$script = <<<EOT
$(document).ready(function() {
    var moduleIssues = $moduleIssuesJson;
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;
    let timerInterval;
    let seconds = 0;

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

    // Request microphone access
    async function setupRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            
            mediaRecorder.ondataavailable = function(event) {
                audioChunks.push(event.data);
            };

            mediaRecorder.onstop = function() {
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const audioUrl = URL.createObjectURL(audioBlob);
                document.getElementById('recordedAudio').src = audioUrl;
                document.getElementById('recordingsList').style.display = 'block';
                audioChunks = [];
            };
        } catch (err) {
            console.error('Error accessing microphone:', err);
            Swal.fire({
                icon: 'error',
                title: 'Microphone Access Error',
                text: 'Please allow microphone access to record voice notes.'
            });
        }
    }

    // Initialize recording setup
    setupRecording();

    // Record button click handler
    $('#recordButton').click(function() {
        if (!isRecording) {
            mediaRecorder.start();
            isRecording = true;
            $(this).hide();
            $('#stopButton').show();
            $('#recordingStatus').text('Recording...');
            startRecordingTimer();
        }
    });

    // Stop button click handler
    $('#stopButton').click(function() {
        if (isRecording) {
            mediaRecorder.stop();
            isRecording = false;
            $(this).hide();
            $('#recordButton').show();
            $('#recordingStatus').text('');
            stopRecordingTimer();
        }
    });

    function startRecordingTimer() {
        seconds = 0;
        timerInterval = setInterval(updateTimer, 1000);
    }

    function stopRecordingTimer() {
        clearInterval(timerInterval);
    }

    function updateTimer() {
        seconds++;
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        $('#recordingStatus').text('Recording: ' + mins + ':' + (secs < 10 ? '0' : '') + secs);
    }

    // Save voice note handler
    $('#saveVoiceNote').click(function() {
        var audioElement = document.getElementById('recordedAudio');
        if (!audioElement.src) {
            return;
        }

        var formData = new FormData();
        fetch(audioElement.src)
            .then(response => response.blob())
            .then(blob => {
                formData.append('voice_note', blob, 'recording.wav');
                formData.append('_csrf', yii.getCsrfToken());

                $.ajax({
                    url: '/ticket/upload-voice-note',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#voice_note_url').val(response.url);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Voice note uploaded successfully'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Failed',
                                text: response.message || 'Failed to upload voice note'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            text: 'An error occurred while uploading the voice note'
                        });
                    }
                });
            });
    });

    // Discard voice note handler
    $('#discardVoiceNote').click(function() {
        document.getElementById('recordedAudio').src = '';
        document.getElementById('recordingsList').style.display = 'none';
        $('#voice_note_url').val('');
        audioChunks = [];
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
EOT;

$this->registerJs($script);
$this->registerJs("
    let fileInput = document.getElementById('ticket-screenshot');
    let base64Input = document.getElementById('screenshot-base64');
    let uploadStatus = document.getElementById('upload-status');
    let previewContainer = document.getElementById('screenshot-preview-container');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Show processing status
                uploadStatus.style.display = 'block';
                previewContainer.style.display = 'none';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64String = e.target.result;
                    base64Input.value = base64String;
                    
                    // Hide processing, show preview
                    uploadStatus.style.display = 'none';
                    
                    if (document.getElementById('screenshot-preview')) {
                        document.getElementById('screenshot-preview').src = base64String;
                        previewContainer.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Form submission validation
    document.getElementById('ticket-form').addEventListener('submit', function(e) {
        if (!base64Input.value) {
            e.preventDefault();
            alert('Please upload a screenshot before submitting the ticket.');
            return false;
        }
        // Show processing status during form submission
        uploadStatus.style.display = 'block';
        uploadStatus.innerHTML = '<div class=\"alert alert-info\"><i class=\"fas fa-spinner fa-spin\"></i> Submitting ticket with screenshot...</div>';
    });
");
?>

<div class="container py-4 ticket-create">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow">
                <div class="card-header text-white text-center" style="background-color: ;">
                    <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="card-body p-4">

                    <!-- Success and Error Alerts -->
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= Yii::$app->session->getFlash('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= Yii::$app->session->getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin([
                        'options' => [
                            'enctype' => 'multipart/form-data',
                            'id' => 'ticket-form'
                        ]
                    ]); ?>

                    <!-- Company Name -->
                    <div class="mb-3">
                        <?= $form->field($model, 'company_name')->textInput([
                            'value' => Yii::$app->user->identity->company_name,
                            'readonly' => true,
                            'class' => 'form-control bg-light'
                        ]) ?>
                    </div>

                    <!-- Module & Issue -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $form->field($model, 'module')->dropDownList(
                                $companyModules,
                                ['prompt' => 'Select Module', 'id' => 'ticket-module', 'class' => 'form-select']
                            ) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $form->field($model, 'issue')->dropDownList(
                                [],
                                ['prompt' => 'Select Issue', 'id' => 'ticket-issue', 'class' => 'form-select']
                            )->hint('Select a module first') ?>
                        </div>
                    </div>

                    <!-- Severity -->
                    <div class="mb-3">
                        <?= $form->field($model, 'severity')->dropDownList(
                            [
                                Ticket::SEVERITY_LOW => 'Low',
                                Ticket::SEVERITY_MEDIUM => 'Medium',
                                Ticket::SEVERITY_HIGH => 'High',
                                Ticket::SEVERITY_CRITICAL => 'Critical'
                            ],
                            ['prompt' => 'Select Severity', 'class' => 'form-select']
                        ) ?>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <?= $form->field($model, 'description')->textarea(['rows' => 4, 'class' => 'form-control']) ?>
                    </div>

                    <!-- Screenshot Upload -->
                    <div class="mb-3">
                        <?= $form->field($model, 'screenshot')->fileInput([
                            'id' => 'ticket-screenshot',
                            'class' => 'form-control',
                            'accept' => 'image/*',
                            'required' => true
                        ])->hint('Screenshot is required. Allowed file types: PNG, JPG, JPEG, GIF. Max size: 5MB') ?>
                        
                        <?= Html::hiddenInput('screenshot-base64', '', ['id' => 'screenshot-base64']) ?>
                        
                        <!-- Add upload status indicator -->
                        <div id="upload-status" class="mt-2" style="display:none;">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin"></i> Processing screenshot...
                            </div>
                        </div>
                    </div>

                    <!-- Preview container -->
                    <div id="screenshot-preview-container" style="display:none; margin-top:10px;">
                        <img id="screenshot-preview" style="max-width:100%; max-height:300px;" />
                        <div class="alert alert-success mt-2">
                            Screenshot ready for upload
                        </div>
                    </div>

                    <!-- Voice Note Recording -->
                    <div class="p-3 rounded" style="background-color: #f8f9fa;">
                        <label class="form-label fw-bold">Voice Note</label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="recordButton" class="btn text-white" style="background-color:#5F6B72">
                                <i class="fas fa-microphone"></i> Start Recording
                            </button>
                            <button type="button" id="stopButton" class="btn btn-danger" style="display:none;">
                                <i class="fas fa-stop"></i> Stop Recording
                            </button>
                            <span id="recordingStatus" class="text-muted ms-2"></span>
                        </div>
                        <div id="recordingsList" class="mt-2" style="display:none;">
                            <audio id="recordedAudio" controls class="w-100"></audio>
                            <input type="hidden" name="voice_note_url" id="voice_note_url">
                            <div class="mt-2">
                                <button type="button" id="saveVoiceNote" class="btn btn-success btn-sm">
                                    <i class="fas fa-save"></i> Save Voice Note
                                </button>
                                <button type="button" id="discardVoiceNote" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Discard
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mt-4">
                        <?= Html::submitButton('Create Ticket', [
                            'class' => 'btn custom-btn text-white p-2',
                            'style' => 'background-color: ; max-width: 200px;',
                            'id' => 'submit-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Container Styling */
    .container {
        max-width: 100%;
        padding: 0px;
    }

    .finefooter {
        padding: 0px 60px;
        margin-bottom: -20px;
    }

    .btn-str {
        background-color: #748386;
        color: white;
        border: none;
    }

    .custom-btn {
        background-color: #EA5626;
        color: white;
        border: none;
    }

    .custom-btn:hover {
        background-color: #C7471E;
        color: white;
    }

    .ticket-create {
        margin-top: 10px;
    }

    .voice-note-section {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }

    #recordingStatus {
        font-family: monospace;
    }

    #recordedAudio {
        width: 100%;
        max-width: 300px;
    }
</style>