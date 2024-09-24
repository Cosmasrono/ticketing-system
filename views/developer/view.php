<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Developer Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-developer-dashboard">
    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Assigned Tickets</h2>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'title',
            'description',
            'status',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{close}',
                'buttons' => [
                    'close' => function ($url, $model, $key) use ($developer) {
                        if ($model->assigned_to == $developer->id && $model->status !== 'closed') {
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
</div>