<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Company Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-view">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php Pjax::begin(['id' => 'ticket-pjax']); ?>
    <?php if ($hasResults): ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'title',
                'status',
                [
                    'attribute' => 'company_email',
                    'value' => function ($model) use ($companyEmail) {
                        return $companyEmail;
                    },
                ],
                'created_at:datetime',
            ],
        ]); ?>
    <?php else: ?>
        <div class="alert alert-info">
            No tickets found for <?= Html::encode($companyEmail) ?>. 
            <?= Html::a('Create a new ticket', ['create'], ['class' => 'alert-link']) ?>
        </div>
    <?php endif; ?>
    <?php Pjax::end(); ?>
</div>