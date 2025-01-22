<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->company_name;
?>

<style>
.client-view {
    padding: 20px;
}

.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.client-details-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.badge-module {
    background: #e3e6f0;
    color: #4e73df;
    padding: 0.4em 0.8em;
    border-radius: 50px;
    font-size: 0.75rem;
    margin: 0.2rem;
    display: inline-block;
}
</style>

<div class="client-view">
    <div class="view-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= Html::encode($this->title) ?></h1>
            <p class="mb-0 text-gray-600">Client Details</p>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Back', ['index'], ['class' => 'btn btn-secondary ml-2']) ?>
        </div>
    </div>

    <div class="card client-details-card">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'company_name',
                    'company_email:email',
                    [
                        'attribute' => 'module',
                        'format' => 'raw',
                        'value' => function($model) {
                            $modules = explode(',', $model->module);
                            $html = [];
                            foreach ($modules as $module) {
                                if (trim($module)) {
                                    $html[] = '<span class="badge-module">' . Html::encode(trim($module)) . '</span>';
                                }
                            }
                            return implode(' ', $html);
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['datetime', 'php:F d, Y h:i A']
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => ['datetime', 'php:F d, Y h:i A']
                    ],
                ],
                'options' => ['class' => 'table table-striped'],
            ]) ?>
        </div>
    </div>
</div> 