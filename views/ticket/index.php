<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

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

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'module',
            'issue',
            'description',
            'status',
            // 'screenshot',

            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->screenshot) {
                        return Html::img('data:image/png;base64,' . $model->screenshot, [
                            'alt' => 'Ticket Screenshot',
                            'style' => 'max-width: 100px; max-height: 100px;'
                        ]);
                    }
                    return 'No screenshot';
                },
            ],
            'created_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete} {reopen}',
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
                    'reopen' => function ($url, $model, $key) {
                        if ($model->status === 'closed') {
                            return Html::a('Reopen', '#', [
                                'class' => 'btn btn-primary btn-sm',
                                'onclick' => new \yii\web\JsExpression("
                                    reopenTicket(this, {$model->id});
                                    return false;
                                "),
                                'data-id' => $model->id,
                            ]);
                        }
                        return '';
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<script>
function reopenTicket(button, id) {
    if (!confirm('Are you sure you want to reopen this ticket?')) {
        return;
    }

    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/reopen']) ?>',
        type: 'POST',
        data: {
            id: id,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Find the row
                var row = $(button).closest('tr');
                
                // Update the status cell (assuming it's the 5th column, adjust index if needed)
                row.find('td:eq(4)').text('reopen');
                
                // Remove the reopen button
                $(button).remove();
                
                alert('Ticket reopened successfully');
            } else {
                alert(response.message || 'Failed to reopen ticket');
                console.error('Error details:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error reopening ticket: ' + error);
        }
    });
}
</script>

<style>
.badge {
    padding: 
}
.badge.bg-info {
    background-color: #17a2b8 !important;
    color: #fff;
}
</style>
