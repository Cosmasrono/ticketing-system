<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'title',
            'description:ntext',
            'status',
            'created_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{approve} {assign} {cancel}',
                'buttons' => [
                    'approve' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'cancelled' || $model->status === 'approved';
                        return Html::button('Approve', [
                            'class' => 'btn btn-sm btn-success' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => $isDisabled ? 'return false;' : "ticketAction('approve', $model->id)",
                        ]);
                    },
                    'assign' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'cancelled' || $model->assigned_to !== null;
                        return Html::button('Assign', [
                            'class' => 'btn btn-sm btn-primary' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => $isDisabled ? 'return false;' : "ticketAction('assign', $model->id)",
                        ]);
                    },
                    'cancel' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'cancelled';
                        return Html::button('Cancel', [
                            'class' => 'btn btn-sm btn-danger' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => $isDisabled ? 'return false;' : "ticketAction('cancel', $model->id)",
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?php
$this->registerJs("
    function ticketAction(action, id) {
        $.ajax({
            url: '" . Url::to(['ticket/action']) . "',
            type: 'POST',
            data: {action: action, id: id},
            success: function(response) {
                if (response.success) {
                    $.pjax.reload({container:'#w0'});
                } else {
                    alert('Action failed: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while processing your request');
            }
        });
    }
");
?>