<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'My Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ticket', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'company_name',
                'value' => function($model) {
                    return !empty($model->company_name) ? $model->company_name : Yii::$app->user->identity->company_name;
                }
            ],
            [
                'attribute' => 'module',
                'value' => function($model) {
                    return !empty($model['module']) ? $model['module'] : '(not set)';
                }
            ],
            [
                'attribute' => 'issue',
                'value' => function($model) {
                    return !empty($model['issue']) ? $model['issue'] : '(not set)';
                }
            ],
            
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'contentOptions' => ['style' => 'max-width:300px; overflow:hidden; text-overflow:ellipsis;'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model) {
                    $statusClasses = [
                        'pending' => 'badge bg-warning',
                        'closed' => 'badge bg-secondary',
                        'escalated' => 'badge bg-danger',
                    ];
                    $class = isset($statusClasses[$model->status]) ? $statusClasses[$model->status] : 'badge bg-primary';
                    return Html::tag('span', $model->status, ['class' => $class]);
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'label' => 'Attachments',
                'format' => 'raw',
                'value' => function ($model) {
                    $ticketData = Yii::$app->db->createCommand('
                        SELECT screenshot_url, voice_note_url 
                        FROM ticket 
                        WHERE id = :id
                    ')
                    ->bindValue(':id', $model->id)
                    ->queryOne();
                    
                    $buttons = [];
                    
                    // Screenshot button
                    if (!empty($ticketData['screenshot_url'])) {
                        $buttons[] = Html::button('<i class="fas fa-image"></i> Screenshot', [
                            'class' => 'btn btn-orange btn-xs view-screenshot mb-1',
                            'data-src' => $ticketData['screenshot_url'],
                            'title' => 'View Screenshot'
                        ]);
                    }
                    
                    // Voice note button
                    if (!empty($ticketData['voice_note_url'])) {
                        $buttons[] = Html::button('<i class="fas fa-microphone"></i> Voice Note', [
                            'class' => 'btn btn-info btn-xs view-voice-note mb-1',
                            'data-src' => $ticketData['voice_note_url'],
                            'title' => 'Play Voice Note'
                        ]);
                    }
                    
                    if (empty($buttons)) {
                        return '<span class="text-muted">No attachments</span>';
                    }
                    
                    return implode('<br>', $buttons);
                },
                'contentOptions' => ['class' => 'text-center', 'style' => 'min-width:120px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete} {close}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-xs',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this ticket?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'close' => function ($url, $model, $key) {
                        if ($model->status !== 'closed') {
                            return Html::button('<i class="fas fa-times-circle"></i> Close', [
                                'class' => 'btn btn-warning btn-xs close-ticket',
                                'data-id' => $model->id,
                                'type' => 'button',
                                'title' => 'Close this ticket'
                            ]);
                        }
                        return '<span class="badge bg-secondary">Closed</span>';
                    },
                ],
                'contentOptions' => ['style' => 'min-width:200px;'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<!-- Update the voice note modal structure -->
<div class="modal fade" id="voiceNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Voice Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <audio id="voiceNotePlayer" controls class="w-100">
                    <source src="" type="audio/wav">
                    Your browser does not support the audio element.
                </audio>
                <div class="mt-3">
                    <a id="downloadVoiceNote" href="" download class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.grid-view td {
    white-space: normal !important;
    vertical-align: middle !important;
}

.badge {
    padding: 5px 10px;
    font-size: 12px;
}

.btn-xs {
    padding: 1px 5px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 3px;
}

.ticket-screenshot {
    max-width: 100%;
    height: auto;
    display: block;
}

.btn-orange {
    background-color: #ff9800; /* Orangish color */
    border-color: #ff9800;
    color: #fff;
}

.btn-orange:hover {
    background-color: #e68900; /* Darker shade on hover */
    border-color: #e68900;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #000;
}

.close-ticket {
    margin-left: 5px;
}

.badge.bg-secondary {
    font-size: 11px;
    padding: 4px 8px;
}

.swal-image-custom {
    max-width: 100%;
    max-height: 70vh; /* Limit height to 70% of viewport height */
    width: auto;
    height: auto;
    object-fit: contain;
    margin: 0 auto;
}

.swal2-popup {
    padding: 1em !important;
}

.swal2-content {
    padding: 0 !important;
}

/* Optional: Add a subtle border and shadow to the image */
.swal-image-custom {
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Ensure modal is not too tall on smaller screens */
@media (max-height: 768px) {
    .swal-image-custom {
        max-height: 60vh;
    }
}

/* Add these new styles */
#voiceNoteModal .modal-body {
    padding: 20px;
}

#voiceNotePlayer {
    width: 100%;
    max-width: 100%;
    margin-bottom: 10px;
}

.btn-xs {
    margin: 2px;
    min-width: 100px;
}

.modal-dialog {
    max-width: 500px;
}

.audio-controls {
    margin-top: 10px;
}

#downloadVoiceNote {
    text-decoration: none;
    margin-top: 10px;
}

#voiceNoteModal .modal-content {
    border-radius: 8px;
}

#voiceNoteModal .modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
}
</style>

<?php
$this->registerJs("
    // Screenshot viewer
    $(document).on('click', '.view-screenshot', function() {
        const imageUrl = $(this).data('src');
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Screenshot',
            width: '60%',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'swal-image-custom'
            }
        });
    });

    // Voice note player
    $(document).on('click', '.view-voice-note', function() {
        const audioUrl = $(this).data('src');
        const player = document.getElementById('voiceNotePlayer');
        const downloadBtn = document.getElementById('downloadVoiceNote');
        
        // Set the audio source
        player.src = audioUrl;
        
        // Set download link
        downloadBtn.href = audioUrl;
        
        // Show modal
        const voiceNoteModal = new bootstrap.Modal(document.getElementById('voiceNoteModal'));
        voiceNoteModal.show();
        
        // Play the audio
        player.load(); // Reload the audio element
        
        // Reset audio when modal is closed
        $('#voiceNoteModal').on('hidden.bs.modal', function () {
            player.pause();
            player.currentTime = 0;
        });
    });

    // Close ticket functionality
    $(document).on('click', '.close-ticket', function(e) {
        e.preventDefault();
        const ticketId = $(this).data('id');
        const button = $(this);
        
        Swal.fire({
            title: 'Close Ticket',
            text: 'Are you sure you want to close this ticket?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, close it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we close the ticket',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '" . Yii::$app->urlManager->createUrl(['ticket/close']) . "',
                    type: 'POST',
                    data: {
                        id: ticketId,
                        _csrf: '" . Yii::$app->request->csrfToken . "'
                    },
                    dataType: 'json'
                })
                .done(function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Ticket Closed!',
                            text: 'The ticket has been successfully closed.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            button.replaceWith('<span class=\"badge bg-secondary\">Closed</span>');
                            button.closest('tr').find('td:nth-child(5)').html(
                                '<span class=\"badge bg-secondary\">closed</span>'
                            );
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to close ticket'
                        });
                    }
                })
                .fail(function(jqXHR) {
                    let errorMessage = 'An error occurred while closing the ticket.';
                    try {
                        const response = JSON.parse(jqXHR.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                });
            }
        });
    });
");
?>

<!-- Add SweetAlert2 CDN -->
<?php $this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_HEAD]); ?>

<!-- Add Font Awesome for the eye icon -->
<?php $this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'); ?>
