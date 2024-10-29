<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */

$this->title = 'Ticket #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->isAdmin()): ?>
    <p>
        <?= Html::a('Approve', ['approve', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Assign', ['assign', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancel', ['cancel', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
    </p>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'module',
            'issue',
            'description:ntext',
            'status',
            'created_at:datetime',
            'company_email',
            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->screenshot) {
                        return Html::img('data:image/png;base64,' . $model->screenshot, [
                            'class' => 'img-responsive',
                            'style' => 'max-width:800px; margin:10px 0; border:1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1);'
                        ]);
                    } else {
                        return '<span class="not-set">(not set)</span>';
                    }
                },
                'labelOptions' => ['class' => 'h4'], // Makes the label bigger
                'contentOptions' => ['class' => 'text-center'], // Centers the image
            ],
        ],
    ]) ?>

</div>
