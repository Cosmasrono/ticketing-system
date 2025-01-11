<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $companyModules array */

$this->title = 'Create Ticket';
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'company_name')->textInput([
        'value' => Yii::$app->user->identity->company_name,
        'readonly' => true
    ]) ?>

    <?= $form->field($model, 'module')->dropDownList(
        array_combine($companyModules, $companyModules),
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
    ) ?>

    <?= $form->field($model, 'severity_level')->dropDownList(
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

    <!-- Voice Note Section -->
    <div class="form-group">
        <label for="voice-note">Voice Note</label>
        <button type="button" id="start-recording" class="btn btn-primary">Start Recording</button>
        <button type="button" id="stop-recording" class="btn btn-danger" disabled>Stop Recording</button>
        <audio id="audio-playback" controls style="display:none;"></audio>
        <input type="hidden" id="voice-note" name="Ticket[voice_note_url]" value="">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Create Ticket', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$moduleIssuesJson = Json::encode($moduleIssues);
$script = <<<JS
    var moduleIssues = {$moduleIssuesJson};
    
    $('#ticket-module').change(function() {
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

    let mediaRecorder;
    let audioChunks = [];

    document.getElementById('start-recording').onclick = async function() {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        
        mediaRecorder.start();
        document.getElementById('start-recording').disabled = true;
        document.getElementById('stop-recording').disabled = false;

        mediaRecorder.ondataavailable = function(event) {
            audioChunks.push(event.data);
        };

        mediaRecorder.onstop = function() {
            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
            const audioUrl = URL.createObjectURL(audioBlob);
            document.getElementById('audio-playback').src = audioUrl;
            document.getElementById('audio-playback').style.display = 'block';
            document.getElementById('voice-note').value = audioUrl; // Store the audio URL in the hidden input
            audioChunks = [];
        };
    };

    document.getElementById('stop-recording').onclick = function() {
        mediaRecorder.stop();
        document.getElementById('start-recording').disabled = false;
        document.getElementById('stop-recording').disabled = true;
    };
JS;
$this->registerJs($script);
?>