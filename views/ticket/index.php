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
            'module',
            'issue',
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
                'attribute' => 'screenshot_base64',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->screenshot_base64)) {
                        $mimeType = mime_content_type('data://application/octet-stream;base64,' . base64_encode($model->screenshot_base64));
                        $imgSrc = sprintf('data:%s;base64,%s', $mimeType, base64_encode($model->screenshot_base64));
                        return '<img src="' . Html::encode($imgSrc) . '" alt="Ticket Screenshot" class="ticket-screenshot" loading="lazy" />';
                    } else {
                        return '<span class="text-muted">No screenshot</span>';
                    }
                },
                'label' => 'Screenshot',
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
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
                        if ($model->status !== 'pending' && $model->created_by == Yii::$app->user->id) {
                            return Html::button('Close', [
                                'class' => 'btn btn-danger btn-sm close-ticket',
                                'data-id' => $model->id,
                                'type' => 'button',
                                'title' => 'Close this ticket'
                            ]);
                        }
                        return '';
                    },
                ],
                'contentOptions' => ['style' => 'min-width:150px;'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

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
</style>

<?php
$this->registerJs("
    $(document).ready(function() {
        $(document).on('click', '.close-ticket', function(e) {
            e.preventDefault();
            const ticketId = $(this).data('id');
            if (ticketId) {
                closeTicket(ticketId);
            }
        });
    });

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
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
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
    }
");
?>

<!-- Add SweetAlert2 CDN -->
<?php $this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_HEAD]); ?>
