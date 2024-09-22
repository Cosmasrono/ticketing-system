<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'title',
            'description',
            'status',
            [
                'attribute' => 'company_email',
                'label' => 'Company Email',
                'value' => function ($model) {
                    return $model->company_email ?? 'No email set';
                },
                'contentOptions' => ['style' => 'font-weight: bold; color: #1a73e8;'],
            ],
            'created_at:datetime',
            [
                'attribute' => 'developer.name',
                'label' => 'Assigned Developer',
                'value' => function ($model) {
                    return $model->developer ? $model->developer->name : 'Not Assigned';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{approve} {assign} {cancel}',
                'buttons' => [
                    'approve' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'approved';
                        return Html::a('Approve', '#', [
                            'class' => 'btn btn-success' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Approve Ticket',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("approveTicket($(this), {$model->id})"),
                            'data-id' => $model->id,
                        ]);
                    },
                    'assign' => function ($url, $model, $key) {
                        $isDisabled = $model->assigned_to !== null;
                        return Html::a('Assign', '#', [
                            'class' => 'btn btn-primary' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Assign to Dev',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("assignTicket($(this), {$model->id})"),
                            'data-id' => $model->id,
                        ]);
                    },
                    'cancel' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'approved' || ($model->assigned_to !== null && $model->status !== 'pending');
                        return Html::a('Cancel', '#', [
                            'class' => 'btn btn-danger' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Cancel Ticket',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("cancelTicket($(this))"),
                            'data-id' => $model->id,
                        ]);
                    },
                ],
            ],
            [
                'label' => 'Debug Info',
                'value' => function ($model) {
                    return '<pre>' . var_export($model->attributes, true) . '</pre>';
                },
                'format' => 'raw',
            ],
        ],
    ]); ?>
</div>