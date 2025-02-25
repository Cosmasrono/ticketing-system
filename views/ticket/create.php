<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Json;
use app\models\Ticket;

$this->title = 'Create Ticket';
?>

<div class="ticket-create">
    <h1 class="text-black"><?= Html::encode($this->title) ?></h1>

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

    <!-- Voice Note Recording Section -->
    <div class="voice-note-section mb-4">
        <label class="form-label">Voice Note</label>
        <div class="d-flex align-items-center gap-2 mb-2">
            <button type="button" id="recordButton" class="btn btn-primary">
                <i class="fas fa-microphone"></i> Start Recording
            </button>
            <button type="button" id="stopButton" class="btn btn-danger" style="display:none;">
                <i class="fas fa-stop"></i> Stop Recording
            </button>
            <span id="recordingStatus" class="text-muted ms-2"></span>
        </div>
        <div id="recordingsList" class="mt-2" style="display:none;">
            <audio id="recordedAudio" controls></audio>
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

    <!-- Image preview container -->
    <div class="preview-container mt-2 mb-3" style="display:none;">
        <img id="image-preview" src="" alt="Preview" style="max-width: 200px;" class="img-thumbnail">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Create Ticket', [
            'class' => 'btn btn-success w-100 p-2 mt-3', 'style' => 'max-width: 200px;',
            'id' => 'submit-button'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
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
?>

<style>
/* ... your existing styles ... */

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