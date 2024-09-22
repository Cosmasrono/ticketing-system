<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Developer Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="developer-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Welcome, <?= Html::encode($developerName) ?>!</p>
    <p>Your Developer ID is: <?= Html::encode($developerId) ?></p>

    <h2>Your Assigned Tickets</h2>
    
    <?php if ($ticketCount > 0): ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'title',
                'description',
                'status',
                'created_at:datetime',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {close}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('View', ['ticket/view', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']);
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a('Update', ['ticket/update', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']);
                        },
                        'close' => function ($url, $model, $key) {
                            if ($model->status !== 'closed') {
                                return Html::a('Close', ['close-ticket', 'id' => $model->id], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to close this ticket?',
                                        'method' => 'post',
                                    ],
                                ]);
                            }
                            return '';
                        },
                    ],
                ],
            ],
        ]); ?>
    <?php else: ?>
        <p>You currently have no assigned tickets.</p>
    <?php endif; ?>
</div>