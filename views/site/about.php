<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $developers app\models\User[] */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about container mt-5">
    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Our Developers</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($developers)): ?>
                <ul>
                    <?php foreach ($developers as $developer): ?>
                        <li><?= Html::encode($developer->name) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No developers found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>All Tickets</h3>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    'title',
                    'status',
                    'created_at:datetime',
                    [
                        'attribute' => 'assigned_to',
                        'value' => function ($model) {
                            return $model->developer ? $model->developer->name : 'Not Assigned';
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
