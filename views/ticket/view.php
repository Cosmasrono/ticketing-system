<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */

$this->title = 'Ticket #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$ticketData = Yii::$app->db->createCommand('SELECT module, issue, screenshot_url, voice_note_url FROM ticket WHERE id = :id')
    ->bindValue(':id', $model->id)
    ->queryOne();

Yii::debug('Raw ticket data from DB: ' . print_r($ticketData, true));
?>

<div class="ticket-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->isAdmin()): ?>
    <p>
        <?= Html::a('Approve', ['approve', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Assign', ['assign', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if (Yii::$app->user->identity->user_type === 'developer'): ?>
            <?= Html::button('Close Ticket', [
                'class' => 'btn btn-danger',
                'onclick' => 'initiateClose()',
                'id' => 'closeButton'
            ]) ?>

            <div id="closeCountdown" style="display: none;" class="alert alert-warning">
                <div class="text-center">
                    <h4>Ticket will be closed in:</h4>
                    <div class="timer-display">
                        <span id="countdown">60</span> seconds
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'module',
                'value' => function($model) use ($ticketData) {
                    return !empty($ticketData['module']) ? $ticketData['module'] : '(not set)';
                },
            ],
            [
                'attribute' => 'issue',
                'value' => function($model) use ($ticketData) {
                    return !empty($ticketData['issue']) ? $ticketData['issue'] : '(not set)';
                },
            ],
            'description:ntext',
            'status',
            'created_at:datetime',
            [
                'attribute' => 'screenshot_url',
                'format' => 'raw',
                'value' => function ($model) use ($ticketData) {
                    $screenshotUrl = !empty($ticketData['screenshot_url']) ? $ticketData['screenshot_url'] : null;
                    if ($screenshotUrl) {
                        return Html::button('<i class="fas fa-eye"></i> View', [
                            'class' => 'btn btn-info btn-sm view-screenshot',
                            'data-src' => $screenshotUrl,
                            'title' => 'View Screenshot'
                        ]);
                    }
                    return '<span class="text-muted">No screenshot available</span>';
                },
            ],
            [
                'attribute' => 'voice_note_url',
                'format' => 'raw',
                'value' => function ($model) use ($ticketData) {
                    $voiceNoteUrl = !empty($ticketData['voice_note_url']) ? $ticketData['voice_note_url'] : null;
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
        width: '80%',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            image: 'swal-image-custom'
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
</style>

<?php
// Debug output at the bottom
echo '<div style="display:none;">';
echo 'Debug Data:<br>';
echo 'Ticket ID: ' . $model->id . '<br>';
echo 'Module: ' . ($ticketData['module'] ?? 'null') . '<br>';
echo 'Issue: ' . ($ticketData['issue'] ?? 'null') . '<br>';
echo '</div>';
?>
