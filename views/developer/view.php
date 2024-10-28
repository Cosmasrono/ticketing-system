<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Developer Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="developer-dashboard">

    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Welcome, <?= Html::encode($user->name) ?></h2>
    <p>Email: <?= Html::encode($user->company_email) ?></p>

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
              
            ],
            'module',
            'issue',
            'status',
            'created_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{escalate} {close}',
                'buttons' => [
                    'escalate' => function ($url, $model, $key) {
                        return Html::a('Escalate', ['developer/escalate-ticket', 'id' => $model->id], [
                            'class' => 'btn btn-warning btn-sm',
                            'data' => [
                                'confirm' => 'Are you sure you want to escalate this ticket?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'close' => function ($url, $model, $key) {
                        return Html::a('Close', ['developer/close-ticket', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Are you sure you want to close this ticket?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
                'visibleButtons' => [
                    'approve' => function ($model, $key, $index) {
                        return $model->status !== 'closed' && $model->status !== 'approved';
                    },
                    'close' => function ($model, $key, $index) {
                        return $model->status !== 'closed';
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>
