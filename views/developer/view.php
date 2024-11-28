<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Ticket;
use app\models\User;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Developer Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="developer-dashboard">

    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Welcome, <?= Html::encode($user->name) ?></h2>
    <p>Email: <?= Html::encode($user->company_email) ?></p>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <h3>Tickets Assigned to You</h3>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'module',
            'issue',
            'description:ntext',
            'status',
            [
                'attribute' => 'escalation_comment',
                'format' => 'raw',
                'label' => 'Escalation Comment',
                'value' => function ($model) {
                    if ($model->escalated_to === Yii::$app->user->id) {
                        return Html::tag('div', 
                            Html::encode($model->escalation_comment) . 
                            '<br><small class="text-muted">Escalated by: ' . 
                            Html::encode(User::findOne($model->escalated_by)->name ?? 'Unknown') . 
                            '</small>',
                            ['class' => 'alert alert-warning']
                        );
                    }
                    return '';  // Return empty if not escalated to this user
                },
            ],
            // company name
            [
                'attribute' => 'company_name',
                'label' => 'Company',
                'value' => function ($model) {
                    // Get company name directly from the ticket's created_by user
                    $createdByUser = $model->getCreatedBy()->one();
                    if ($createdByUser && $createdByUser->company_name) {
                        return Html::encode($createdByUser->company_name);
                    }
                    
                    // Fallback to ticket's company_name if available
                    if (!empty($model->company_name)) {
                        return Html::encode($model->company_name);
                    }
                    
                    return '<span class="text-muted">Not Available</span>';
                },
                'format' => 'raw',
            ],
            'created_at:datetime',
            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'label' => 'Screenshot',
                'contentOptions' => ['class' => 'text-center'],
                'value' => function ($model) {
                    if ($model->screenshot_base64) {
                        return Html::a('View', '#', [
                            'class' => 'btn btn-info btn-sm view-screenshot',
                            'data-screenshot' => $model->screenshot_base64,
                        ]);
                    }
                    return '<span class="text-muted">(no screenshot)</span>';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{escalate}',
                'buttons' => [
                    'escalate' => function ($url, $model, $key) {
                        $isDisabled = $model->status === Ticket::STATUS_ESCALATED || 
                                      $model->status === 'closed' || 
                                      $model->assigned_to !== Yii::$app->user->id;
                        return Html::button('Escalate', [
                            'class' => 'btn btn-warning btn-sm' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => !$isDisabled ? "escalateTicket({$model->id})" : 'return false;',
                            'data-id' => $model->id,
                        ]);
                    },
                    
                ],
                'visibleButtons' => [
                    'approve' => function ($model, $key, $index) {
                        return $model->status !== 'closed' && $model->status !== 'approved';
                    },
                    'close' => function ($model, $key, $index) {
                        return $model->status !== 'closed';
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>
<!-- Modal for Screenshot -->
<div class="modal fade" id="screenshotModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Screenshot</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="fullScreenshot" class="img-responsive" style="max-width:100%; height:auto;" />
            </div>
        </div>
    </div>
</div>

<!-- Add this modal HTML right after your screenshot modal -->
<div class="modal fade" id="escalateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Escalate Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="escalationComment">Escalation Comment</label>
                    <textarea id="escalationComment" class="form-control" rows="4" 
                        placeholder="Please provide a reason for escalation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitEscalation()">Escalate</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Escalation Comment -->
<div class="modal fade" id="escalationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> 
                    Escalation Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="escalation-content">
                    <div class="escalation-info mb-3">
                        <strong>Escalated by:</strong> <span class="escalated-by"></span>
                    </div>
                    <div class="escalation-message"></div>
                    <div class="escalation-meta mt-3">
                        <small class="text-muted">
                            <i class="far fa-clock"></i> 
                            <span class="escalated-at"></span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-lg {
    max-width: 90%;
}

.modal-body {
    padding: 20px;
}

.img-responsive {
    max-width: 100%;
    height: auto;
}

.view-screenshot {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.view-screenshot:hover {
    background-color: #138496;
    border-color: #117a8b;
    color: #fff;
    text-decoration: none;
}

.close-ticket {
    cursor: pointer;
}

.close-ticket.disabled {
    opacity: 0.65;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.escalation-info {
    margin: 10px 0;
}

.escalation-text {
    white-space: pre-wrap;
    word-break: break-word;
    line-height: 1.5;
    padding: 10px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 4px;
}

.escalation-alert {
    margin-top: 10px;
}

.escalation-header {
    font-weight: bold;
    color: #856404;
}

.escalation-message {
    background: rgba(255, 255, 255, 0.7);
    padding: 10px;
    border-radius: 4px;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.9em;
    line-height: 1.4;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    padding: 10px;
}

.escalation-message {
    background: #fff;
    padding: 8px;
    border-radius: 4px;
    font-size: 0.9em;
    line-height: 1.4;
    margin-top: 5px;
    border: 1px solid rgba(0,0,0,0.1);
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
    padding: 8px;
    margin-bottom: 0;
}

.escalation-header {
    margin-bottom: 5px;
    color: #856404;
}

.escalation-header i {
    margin-right: 5px;
}

.view-escalation {
    min-width: 120px;
}

.escalation-content {
    background: #fff3cd;
    border-radius: 4px;
    padding: 15px;
}

.escalation-message {
    background: #fff;
    padding: 15px;
    border-radius: 4px;
    white-space: pre-wrap;
    word-break: break-word;
    line-height: 1.5;
    border: 1px solid rgba(0,0,0,0.1);
}

#escalationModal .modal-header {
    background: #fff3cd;
    border-bottom: 1px solid #ffeeba;
}

#escalationModal .modal-title {
    color: #856404;
}
</style>

<?php
$this->registerJs("
    $(document).on('click', '.view-screenshot', function(e) {
        e.preventDefault();
        var base64Data = $(this).data('screenshot');
        $('#fullScreenshot').attr('src', 'data:image/png;base64,' + base64Data);
        $('#screenshotModal').modal('show');
    });
", \yii\web\View::POS_READY);
?>

<?php
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<!-- Add this right after your other JS includes -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentTicketId = null;

function escalateTicket(ticketId) {
    currentTicketId = ticketId;
    $('#escalationComment').val(''); // Clear previous comments
    $('#escalateModal').modal('show');
}

function submitEscalation() {
    const comment = $('#escalationComment').val().trim();
    
    if (!comment) {
        Swal.fire({
            icon: 'error',
            title: 'Comment Required',
            text: 'Please provide a comment for the escalation.'
        });
        return;
    }

    Swal.fire({
        title: 'Confirm Escalation',
        text: 'Are you sure you want to escalate this ticket?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f0ad4e',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, escalate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make the AJAX request
            $.ajax({
                url: '<?= \yii\helpers\Url::to(['/ticket/escalate']) ?>',
                type: 'POST',
                data: {
                    id: currentTicketId,
                    comment: comment,
                    _csrf: '<?= Yii::$app->request->csrfToken ?>'
                },
                dataType: 'json'
            })
            .done(function(response) {
                $('#escalateModal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Ticket has been escalated successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to escalate ticket'
                    });
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                $('#escalateModal').modal('hide');
                
                let errorMessage = 'An error occurred while escalating the ticket.';
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
}

// Remove the inline onclick handler and use event delegation instead
$(document).ready(function() {
    // For debugging
    console.log('JavaScript loaded');
    
    $(document).on('click', '.close-ticket:not(.disabled)', function(e) {
        e.preventDefault();
        console.log('Close button clicked');
        const ticketId = $(this).data('id');
        if (ticketId) {
            closeTicket(ticketId);
        } else {
            console.error('No ticket ID found on button');
        }
    });
});

function closeTicket(ticketId) {
    console.log('Initiating close for ticket:', ticketId);

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
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make the AJAX request
            $.ajax({
                url: '<?= Yii::$app->urlManager->createUrl(['ticket/close']) ?>',
                type: 'POST',
                data: {
                    id: ticketId,
                    _csrf: '<?= Yii::$app->request->csrfToken ?>'
                },
                dataType: 'json'
            })
            .done(function(response) {
                console.log('Server response:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
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
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', {
                    status: textStatus,
                    statusCode: jqXHR.status,
                    responseText: jqXHR.responseText,
                    error: errorThrown
                });

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
}

// Add this to your existing JavaScript
$(document).on('click', '.view-escalation', function() {
    const comment = $(this).data('comment');
    const ticketId = $(this).data('ticket-id');
    
    $('#escalationModal .escalation-message').html(comment);
    $('#escalationModal .ticket-id').text('Ticket ID: ' + ticketId);
    $('#escalationModal').modal('show');
});
  </script>

<?php
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
?>