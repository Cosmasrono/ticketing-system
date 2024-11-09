<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Ticket;

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
            // company name
            [
                'attribute' => 'company_name',
                'value' => function ($model) {
                    // First try to get company_name directly from ticket
                    if (!empty($model->company_name)) {
                        return $model->company_name;
                    }
                    // If not found, try to get it from the user who created the ticket
                    if ($model->createdBy && $model->createdBy->company_name) {
                        return $model->createdBy->company_name;
                    }
                    return 'Not Assigned';
                }
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
                'template' => '{escalate} {close}',
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
                    'close' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'closed' || 
                                      $model->assigned_to !== Yii::$app->user->id;
                        return Html::button('Close', [
                            'class' => 'btn btn-danger btn-sm close-ticket' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => !$isDisabled ? "closeTicket({$model->id})" : 'return false;',
                            'data-id' => $model->id,
                            'type' => 'button',
                            'title' => $isDisabled ? ($model->status === 'closed' ? 'Ticket is already closed' : 'You can only close your assigned tickets') : 'Close this ticket'
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
function escalateTicket(ticketId) {
    if (confirm('Are you sure you want to escalate this ticket?')) {
        $.ajax({
            url: '<?= \yii\helpers\Url::to(['/ticket/escalate']) ?>',
            type: 'POST',
            data: {
                id: ticketId,
                _csrf: '<?= Yii::$app->request->csrfToken ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // This will refresh the page to show updated status
                } else {
                    alert('Failed to escalate ticket: ' + (response.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('An error occurred while escalating the ticket.');
            }
        });
    }
}

function closeTicket(ticketId) {
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
            $.ajax({
                url: '<?= \yii\helpers\Url::to(['/ticket/close']) ?>',
                type: 'POST',
                data: {
                    id: ticketId,
                    _csrf: '<?= Yii::$app->request->csrfToken ?>'
                },
                dataType: 'json',
                beforeSend: function() {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we close the ticket',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Ticket has been closed successfully',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Reload the page to show updated status
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to close ticket'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while closing the ticket. Please try again.'
                    });
                }
            });
        }
    });
}

// Add event listener for close buttons
$(document).ready(function() {
    // For debugging
    console.log('JavaScript loaded');
    
    $(document).on('click', '.close-ticket', function(e) {
        e.preventDefault();
        console.log('Close button clicked');
        const ticketId = $(this).data('id');
        closeTicket(ticketId);
    });
});
</script>
