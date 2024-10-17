<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Ticket; // Add this line to import the Ticket model

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ticket', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'module',
            'issue',
            'description:ntext',
            'status',
            'created_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{softDelete} {reopen}',
                'buttons' => [
                    'softDelete' => function ($url, $model, $key) {
                        if ($model->status !== Ticket::STATUS_DELETED) {
                            return Html::a('Delete', '#', [
                                'class' => 'btn btn-danger btn-xs soft-delete-ticket',
                                'data-ticket-id' => $model->id,
                            ]);
                        }
                        return '';
                    },
                    'reopen' => function ($url, $model, $key) {
                        if ($model->status === Ticket::STATUS_CLOSED || $model->status === Ticket::STATUS_DELETED) {
                            return Html::a('Reopen', '#', [
                                'class' => 'btn btn-warning btn-xs reopen-ticket',
                                'data-ticket-id' => $model->id,
                            ]);
                        }
                        return '';
                    },
                ],
            ],
        ],
    ]); ?>

</div>

<?php
$this->registerJs("
    $('.soft-delete-ticket').on('click', function(e) {
        e.preventDefault();
        var ticketId = $(this).data('ticket-id');
        if (confirm('Are you sure you want to delete this ticket?')) {
            $.ajax({
                url: '" . \yii\helpers\Url::to(['ticket/soft-delete']) . "',
                type: 'POST',
                data: {id: ticketId},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while trying to delete the ticket.');
                }
            });
        }
    });

    $('.reopen-ticket').on('click', function(e) {
        e.preventDefault();
        var ticketId = $(this).data('ticket-id');
        if (confirm('Are you sure you want to reopen this ticket?')) {
            $.ajax({
                url: '" . \yii\helpers\Url::to(['ticket/reopen']) . "',
                type: 'POST',
                data: {id: ticketId},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        if (response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else {
                            location.reload();
                        }
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while trying to reopen the ticket.');
                }
            });
        }
    });
");
?>
