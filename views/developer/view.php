<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Ticket; // Add this line

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
            [
                'attribute' => 'user.company_name',
              
            ],
            'module',
            'issue',
            'description:ntext',
            'status',
            'created_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{escalate} {close}',
                'buttons' => [
                    'escalate' => function ($url, $model, $key) {
                        // Always disable escalate after reassignment
                        $isDisabled = $model->status === Ticket::STATUS_ESCALATE || 
                                      $model->status === 'closed' || 
                                      $model->assigned_to !== Yii::$app->user->id;
                        return Html::a('Escalate', '#', [
                            'class' => 'btn btn-warning btn-sm disabled', // Always disabled after reassignment
                            'onclick' => 'return false;',
                            'data-id' => $model->id,
                        ]);
                    },
                    'close' => function ($url, $model, $key) {
                        // Enable close button if ticket is assigned to current user and not closed
                        $isDisabled = $model->status === 'closed' || 
                                      $model->assigned_to !== Yii::$app->user->id;
                        return Html::a('Close', '#', [
                            'class' => 'btn btn-danger btn-sm' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => !$isDisabled ? new \yii\web\JsExpression("
                                if(confirm('Are you sure you want to close this ticket?')) {
                                    closeTicket({$model->id});
                                }
                                return false;
                            ") : 'return false;',
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

<script>
function escalateTicket(ticketId) {
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['ticket/escalate']) ?>',
        type: 'POST',
        data: {
            id: ticketId,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Find the row
                var row = $('tr[data-key="' + ticketId + '"]');
                
                // Update status badge
                var statusCell = row.find('td:contains("pending")');
                if (statusCell.length) {
                    statusCell.html('<span class="badge bg-danger">escalated</span>');
                }
                
                // Disable both escalate and close buttons
                row.find('.btn-warning, .btn-danger')
                   .addClass('disabled')
                   .prop('onclick', null)
                   .attr('onclick', 'return false;');
                
                alert('Ticket has been escalated successfully');
            } else {
                alert('Failed to escalate ticket: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error escalating ticket: ' + error);
            console.log('Error details:', xhr.responseText);
        }
    });
}

function closeTicket(ticketId) {
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/close']) ?>',
        type: 'POST',
        data: {
            id: ticketId,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Find the row
                var row = $('tr[data-key="' + ticketId + '"]');
                
                // Update status badge to closed
                row.find('td:contains("escalated")').html('<span class="badge bg-secondary">closed</span>');
                
                // Disable escalate and close buttons in developer view
                row.find('.btn-warning, .btn-danger')
                   .addClass('disabled')
                   .prop('onclick', null)
                   .attr('onclick', 'return false;');
                
                // If in admin view, also disable approve button
                if ($('.admin-grid').length) {
                    var adminRow = $('.admin-grid tr[data-key="' + ticketId + '"]');
                    adminRow.find('.btn-success')
                           .addClass('disabled')
                           .prop('onclick', null)
                           .attr('onclick', 'return false;');
                }
                
                alert('Ticket has been closed successfully');
            } else {
                alert('Failed to close ticket: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error closing ticket: ' + error);
        }
    });
}
</script>

<style>
/* Button and badge styles */
.btn.disabled {
    opacity: 0.65;
    cursor: not-allowed;
    pointer-events: none;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.bg-danger {
    background-color: #dc3545;
    color: #fff;
}

.bg-warning {
    background-color: #ffc107;
    color: #000;
}

.bg-secondary {
    background-color: #6c757d !important;
    color: #fff;
}

.btn-success.disabled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    opacity: 0.65;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.badge.bg-secondary {
    background-color: #6c757d;
    color: #fff;
}
</style>
