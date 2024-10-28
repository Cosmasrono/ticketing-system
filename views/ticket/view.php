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
           // Make sure this is included if you want to display the company name
            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->screenshot) {
                        return Html::img('@web/uploads/' . $model->screenshot, 
                            ['class' => 'img-responsive', 'style' => 'max-width:300px;']);
                    } else {
                        return '<span class="not-set">(not set)</span>';
                    }
                },
            ],
        ],
    ]) ?>

    <?php if ($model->screenshot): ?>
        <h3>Screenshot</h3>
        <img src="data:image/png;base64,<?= $model->screenshot ?>" alt="Ticket Screenshot" style="max-width: 100%; height: auto;">
    <?php endif; ?>

</div>
