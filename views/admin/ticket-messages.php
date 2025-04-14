<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\User;
use app\models\TicketMessage;
use app\components\NotificationHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

// Verify user has permission to access this page
$userRole = Yii::$app->user->identity->role;
$isAdmin = ($userRole == 1 || $userRole == 4 || $userRole === 'admin' || $userRole === 'superadmin');

if (!$isAdmin) {
    NotificationHelper::error('You do not have permission to access this page.');
    echo '<script>window.location.href = "' . \yii\helpers\Url::to(['/site/index']) . '";</script>';
    return;
}

$this->title = 'Ticket Messages';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-messages-index container" style="padding:40px; margin-top:-20px;">
    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        <i class="fas fa-envelope"></i> Message Summary
                    </h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="alert alert-warning">
                                <h5>Unread Messages</h5>
                                <h3><?= $unreadCount ?></h3>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <p class="mb-0">
                                This page displays all messages exchanged between developers and users. 
                                Messages are automatically marked as "viewed" by the admin when you view the message details.
                            </p>
                            <p>
                                <strong>Note:</strong> Internal notes are only visible to admins and the developers who created them.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php Pjax::begin(); ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'ticket_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(
                        'Ticket #' . $model->ticket_id,
                        ['/ticket/view', 'id' => $model->ticket_id],
                        ['target' => '_blank', 'data-pjax' => 0]
                    );
                },
            ],
            [
                'attribute' => 'subject',
                'format' => 'raw',
                'value' => function ($model) {
                    $label = $model->is_internal 
                        ? '<span class="badge bg-secondary">Internal</span> ' 
                        : '';
                    
                    if ($model->message_type === 'closure_message') {
                        $label .= '<span class="badge bg-danger">Closure</span> ';
                    } elseif ($model->message_type === 'system') {
                        $label .= '<span class="badge bg-success">System</span> ';
                    }
                    
                    return $label . Html::encode($model->subject);
                },
            ],
            [
                'attribute' => 'sender_id',
                'label' => 'From',
                'value' => function ($model) {
                    $sender = $model->sender;
                    return $sender 
                        ? Html::encode($sender->name) . ' (' . $sender->role . ')' 
                        : 'Unknown';
                },
            ],
            [
                'attribute' => 'recipient_id',
                'label' => 'To',
                'value' => function ($model) {
                    $recipient = $model->recipient;
                    return $recipient 
                        ? Html::encode($recipient->name) . ' (' . $recipient->role . ')' 
                        : 'Unknown';
                },
            ],
            [
                'attribute' => 'sent_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->sent_at);
                },
            ],
            [
                'attribute' => 'admin_viewed',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->admin_viewed 
                        ? '<span class="badge bg-success">Viewed</span>' 
                        : '<span class="badge bg-warning text-dark">New</span>';
                },
                'contentOptions' => function ($model) {
                    return ['class' => $model->admin_viewed ? '' : 'table-warning'];
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button(
                            '<i class="fas fa-eye"></i> View',
                            [
                                'class' => 'btn btn-info btn-sm view-message',
                                'data-id' => $model->id,
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>
    
    <?php Pjax::end(); ?>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-envelope-open-text"></i> Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="messageContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading message...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$viewMessageUrl = \yii\helpers\Url::to(['admin/view-message']);
$script = <<<JS
$(document).ready(function() {
    $('.view-message').on('click', function() {
        const id = $(this).data('id');
        $('#messageContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading message...</div>');
        $('#messageModal').modal('show');
        
        $.ajax({
            url: '{$viewMessageUrl}',
            type: 'GET',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let messageClass = 'message-item';
                    if (response.message.message_type === 'closure_message') {
                        messageClass += ' message-closure';
                    } else if (response.message.message_type === 'system') {
                        messageClass += ' message-system';
                    }
                    
                    if (response.message.is_internal) {
                        messageClass += ' message-internal';
                    }
                    
                    let html = `
                        <div class="\${messageClass}">
                            <div class="message-header">
                                <h5>\${response.message.subject}</h5>
                                <div class="message-meta">
                                    <span><i class="fas fa-ticket-alt"></i> Ticket #\${response.message.ticket_id}</span>
                                    <span><i class="fas fa-user-edit"></i> From: \${response.message.sender_name}</span>
                                    <span><i class="fas fa-user"></i> To: \${response.message.recipient_name}</span>
                                    <span><i class="far fa-clock"></i> \${response.message.sent_at}</span>
                                </div>
                            </div>
                            <div class="message-divider"></div>
                            <div class="message-content">
                                \${response.message.message}
                            </div>
                        </div>
                    `;
                    
                    $('#messageContent').html(html);
                } else {
                    $('#messageContent').html(`
                        <div class="alert alert-danger">
                            \${response.message || 'Failed to load message.'}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#messageContent').html(`
                    <div class="alert alert-danger">
                        An error occurred while loading the message.
                    </div>
                `);
            }
        });
    });
});
JS;
$this->registerJs($script);
?>

<style>
.message-item {
    padding: 15px;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.message-header {
    margin-bottom: 15px;
}

.message-header h5 {
    margin-bottom: 10px;
    color: #1B1D4E;
}

.message-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: 0.9rem;
    color: #6c757d;
}

.message-meta span {
    display: inline-flex;
    align-items: center;
}

.message-meta i {
    margin-right: 5px;
}

.message-divider {
    height: 1px;
    background-color: #dee2e6;
    margin: 15px 0;
}

.message-content {
    white-space: pre-wrap;
    font-size: 1rem;
    line-height: 1.5;
}

.message-closure {
    border-left: 4px solid #dc3545;
}

.message-system {
    border-left: 4px solid #28a745;
}

.message-internal {
    border-left: 4px solid #6c757d;
    background-color: #f0f0f0;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.2) !important;
}

@media (max-width: 767.98px) {
    .message-meta {
        flex-direction: column;
        gap: 5px;
    }
}
</style> 