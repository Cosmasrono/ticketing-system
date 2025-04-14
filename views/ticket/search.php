<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ticket Search';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-search-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row mb-4">
        <div class="col">
            <form method="get" action="<?= \yii\helpers\Url::to(['ticket/search']) ?>">
                <div class="input-group">
                    <input type="text" name="company_name" class="form-control" placeholder="Enter Company Name" value="<?= Html::encode(Yii::$app->request->get('company_name')) ?>" aria-label="Company Name">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'company_name',
            'issue',
            'status',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>