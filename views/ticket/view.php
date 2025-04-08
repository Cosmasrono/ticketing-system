<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */

$this->title = 'Ticket #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-view">
    <h1><?= Html::encode($this->title) ?></h1>

<?php
    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'module',
                'value' => function($model) {
                    return !empty($model->module) ? $model->module : '(not set)';
                },
            ],
            [
                'attribute' => 'issue',
                'value' => function($model) {
                    return !empty($model->issue) ? $model->issue : '(not set)';
                },
            ],
            'description:ntext',
            'status',
            'created_at:datetime',
            [
                'attribute' => 'screenshot_url',
                'format' => 'raw',
                'value' => function ($model) {
                    $screenshotUrl = !empty($model->screenshot_url) ? $model->screenshot_url : null;
                    if ($screenshotUrl) {
                        return Html::button('<i class="fas fa-eye"></i> View', [
                            'class' => 'btn btn-info btn-sm view-screenshot',
                            'data-src' => $screenshotUrl,
                            'title' => 'View Screenshot',
                            'onclick' => "showFullImage('$screenshotUrl')"
                        ]);
                    }
                    return '<span class="text-muted">No screenshot available</span>';
                },
            ],
            [
                'attribute' => 'voice_note_url',
                'format' => 'raw',
                'value' => function ($model) {
                    $voiceNoteUrl = !empty($model->voice_note_url) ? $model->voice_note_url : null;
                    if ($voiceNoteUrl) {
                        return '
                            <div class="voice-note-player">
                                <audio controls class="audio-player">
                                    <source src="' . Html::encode($voiceNoteUrl) . '" type="audio/wav">
                                    Your browser does not support the audio element.
                                </audio>
                                <a href="' . Html::encode($voiceNoteUrl) . '" 
                                   class="btn btn-sm btn-outline-primary ms-2" 
                                   target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>';
                    }
                    return '<span class="text-muted">No voice note available</span>';
                },
            ],
        ],
    ]) ?>

</div>

<?php
// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

// Register SweetAlert2
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_HEAD]);

$script = <<<JS
$(document).on('click', '.view-screenshot', function() {
    const imageUrl = $(this).data('src');
    Swal.fire({
        imageUrl: imageUrl,
        imageAlt: 'Screenshot',
        width: '80%',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            image: 'swal-image-custom'
        }
    });
});

function initiateClose() {
    Swal.fire({
        title: 'Close Ticket',
        text: 'Are you sure you want to close this ticket?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, close it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#closeCountdown').show();
            let timeLeft = 60;
            
            let countdownTimer = setInterval(function() {
                timeLeft--;
                $('#countdown').text(timeLeft);
                
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    closeTicket();
                }
            }, 1000);
        }
    });
}

function closeTicket() {
    $.ajax({
        url: '/ticket/close',
        type: 'POST',
        data: { 
            id: {$model->id},
            _csrf: yii.getCsrfToken()
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket has been closed.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to close ticket'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while closing the ticket'
            });
        }
    });
}

function showFullImage(url) {
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Screenshot',
        width: '90%',
        padding: '1em',
        showCloseButton: true,
        showConfirmButton: false,
        background: '#fff',
        backdrop: `
            rgba(0,0,0,0.8)
        `,
        customClass: {
            image: 'swal-image-custom',
            closeButton: 'swal2-close-button'
        }
    });
}
JS;
$this->registerJs($script);
?>

<style>
.swal-image-custom {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.view-screenshot {
    min-width: 80px;
}

.swal2-popup {
    padding: 1em;
}

.voice-note-player {
    display: flex;
    align-items: center;
    gap: 10px;
}

.audio-player {
    max-width: 300px;
    height: 40px;
}

.voice-note-player .btn {
    white-space: nowrap;
}

.thumbnail-container {
    max-width: 200px;
    margin: 10px 0;
}

.thumbnail-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.thumbnail-image:hover {
    transform: scale(1.05);
}
</style>

<?php
// Debug output at the bottom
echo '<div style="display:none;">';
echo 'Debug Data:<br>';
echo 'Ticket ID: ' . $model->id . '<br>';
echo 'Module: ' . ($model->module ?? 'null') . '<br>';
echo 'Issue: ' . ($model->issue ?? 'null') . '<br>';
echo '</div>';
?>