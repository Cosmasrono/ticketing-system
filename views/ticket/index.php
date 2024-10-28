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
                            return Html::button('Reopen', [
                                'class' => 'btn btn-primary btn-xs',
                                'onclick' => "reopenTicket(this, $model->id)"
                            ]);
                        }
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<script>
function reopenTicket(button, id) {
    console.log("Reopening ticket with ID:", id);
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/reopen']) ?>',
        type: 'POST',
        data: {
            id: id,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Received response:", response);
            if (response.success) {
                alert(response.alert);
                $(button).closest('tr').find('td:eq(4)').text('open');
                $(button).remove();
            } else {
                alert(response.alert);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error:', textStatus, errorThrown);
            console.error('Response:', jqXHR.responseText);
            alert('An error occurred while processing your request. Please check the console for more details.');
        }
    });
}
</script>

