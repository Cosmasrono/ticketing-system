<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'My Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container ticket-index">

    <h1 class="fw-semibold"> <?= Html::encode($this->title) ?> </h1>

    <p class="text-center">
        <?= Html::a(
            '<i class="fas fa-plus-circle"></i> Create Ticket',
            ['create'],
            [
                'class' => 'btn custom-btn w-100 p-2 mt-3 rounded-1 d-flex align-items-center justify-content-center gap-2',
                'style' => 'max-width: 220px;'
            ]
        )
        ?>
    </p>



    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover shadow-sm custom-table'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'company_name',
                'value' => fn($model) => $model->company_name ?: Yii::$app->user->identity->company_name,
            ],
            [
                'attribute' => 'module',
                'value' => fn($model) => $model['module'] ?: '(not set)',
            ],
            [
                'attribute' => 'issue',
                'value' => fn($model) => $model['issue'] ?: '(not set)',
            ],
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'contentOptions' => ['style' => 'max-width:350px; overflow:hidden; text-overflow:ellipsis;'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    $statusClasses = [
                        'pending' => 'badge bg-warning text-dark',
                        'closed' => 'badge bg-success',
                        'escalated' => 'badge bg-danger',
                    ];
                    $class = $statusClasses[$model->status] ?? 'badge bg-primary';
                    return Html::tag('span', ucfirst($model->status), ['class' => $class]);
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete} {close}',
                'buttons' => [
                    'delete' => fn($url, $model) => Html::a('Delete', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-xs',
                        'data' => ['confirm' => 'Are you sure you want to delete this ticket?', 'method' => 'post'],
                    ]),
                    'close' => function ($url, $model) {
                        if ($model->status !== 'closed') {
                            return Html::button('<i class="fas fa-times-circle"></i> Close', [
                                'class' => 'btn btn-warning btn-xs close-ticket',
                                'data-id' => $model->id,
                                'type' => 'button',
                                'title' => 'Close this ticket'
                            ]);
                        }
                        return '<span class="badge bg-success">Closed</span>';
                    },
                ],
                'contentOptions' => ['style' => 'min-width:180px;'],
            ],
        ],
    ]); ?>


    <?php Pjax::end(); ?>
</div>

<style>
   .custom-table thead th {
   
        color:  #1B1D4E !important;
      
    }

    .custom-btn {
        background-color:#748386;
        /* Primary Blue */
        color: white;
        border: none;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .custom-btn:hover {
        background-color: #5F6B72;
        color: white;

        
    }

    .custom-btn i {
        font-size: 1.2rem;
        /* Slightly larger icon */
    }

    .ticket-index {
        margin-top: 30px;

        padding: 20px;
        border-radius: 8px;
    }

    .table-hover tbody tr:hover {
        background-color: #e3f2fd;
    }

    .btn-outline-primary {
        border-color: #E85720;
        color: #E85720;
    }

    .btn-outline-primary:hover {
        background-color: white;
        color: #1C1C4E;
    }

    .btn-create {
        background-color: #E85720;
        color: white;
        font-weight: normal;
        border-radius: 5%;
    }

    .btn-create:hover {
        background-color: #d04d1c;
        color: white;
    }

    .btn-warning {
        background-color: #ffc107;
        color: black;
    }

    .btn-danger {
        background-color: #dc3545;
    }
</style>