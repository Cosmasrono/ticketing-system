<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $developer app\models\Developer */

$this->title = 'Developer Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="developer-dashboard">

    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Welcome, <?= Html::encode($developer->name) ?></h2>
    <p>Email: <?= Html::encode($developer->company_email) ?></p>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <h3>Tickets Assigned to You</h3>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'user.company_name',
                'label' => 'Reported By',
            ],
            'module',
            'issue',
            'description:ntext',
            'status',
            'created_at:datetime',
  
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{close} {escalate}',
                'buttons' => [
                    'close' => function ($url, $model) use ($developer) {
                        $canClose = $model->status !== 'closed' && $model->status !== 'escalated';
                        return Html::a('Close', ['close-ticket', 'id' => $model->id], [
                            'title' => Yii::t('app', 'Close Ticket'),
                            'data-confirm' => Yii::t('app', 'Are you sure you want to close this ticket?'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                            'class' => 'btn btn-success btn-xs close-btn' . ($canClose ? '' : ' disabled'),
                            'onclick' => $canClose ? null : 'return false;',
                            'data-ticket-id' => $model->id,
                        ]);
                    },
                    'escalate' => function ($url, $model) {
                        $canEscalate = $model->status !== 'escalated' && $model->status !== 'closed';
                        return Html::a('Escalate', ['escalate-ticket', 'id' => $model->id], [
                            'title' => Yii::t('app', 'Escalate Ticket'),
                            'data-confirm' => Yii::t('app', 'Are you sure you want to escalate this ticket?'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                            'class' => 'btn btn-warning btn-xs escalate-btn' . ($canEscalate ? '' : ' disabled'),
                            'onclick' => $canEscalate ? null : 'return false;',
                            'data-ticket-id' => $model->id,
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>

