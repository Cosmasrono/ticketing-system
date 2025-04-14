<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;

$this->title = 'Contract Renewals for ' . $company->name;
$this->params['breadcrumbs'][] = ['label' => 'Contracts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contract-company">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="mb-3">
        <div class="card">
            <div class="card-header">
                <h3>Company Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?= Html::encode($company->name) ?></p>
                        <p><strong>Email:</strong> <?= Html::encode($company->company_email) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Current Contract Start:</strong> <?= Yii::$app->formatter->asDate($company->start_date) ?></p>
                        <p><strong>Current Contract End:</strong> <?= Yii::$app->formatter->asDate($company->end_date) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>All Contract Renewals</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($renewals)): ?>
                <?= GridView::widget([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $renewals,
                        'pagination' => [
                            'pageSize' => 10,
                        ],
                    ]),
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'requested_by',
                            'label' => 'Requested By',
                            'value' => function ($model) {
                                $user = \app\models\User::findOne($model->requested_by);
                                return $user ? Html::encode($user->name) : 'Unknown';
                            },
                        ],
                        [
                            'attribute' => 'renewal_duration',
                            'label' => 'Extension Period',
                            'value' => function ($model) {
                                return $model->renewal_duration . ' Months';
                            },
                        ],
                        'current_end_date:date',
                        'new_end_date:date',
                        'notes',
                        [
                            'attribute' => 'renewal_status',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return $model->getStatusLabel();
                            }
                        ],
                        'created_at:datetime',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {approve} {reject}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a(
                                        '<i class="fas fa-eye"></i>',
                                        ['view', 'id' => $model->id],
                                        ['class' => 'btn btn-info btn-sm', 'title' => 'View']
                                    );
                                },
                                'approve' => function ($url, $model) {
                                    if ($model->renewal_status === 'approved') {
                                        return '<button class="btn btn-success btn-sm" disabled>Approved</button>';
                                    }
                                    return '<button type="button" 
                                                class="btn btn-success btn-sm update-status-btn" 
                                                data-id="' . $model->id . '" 
                                                data-status="approved">
                                            Approve
                                        </button>';
                                },
                                'reject' => function ($url, $model) {
                                    if ($model->renewal_status === 'approved') {
                                        return '';
                                    }
                                    return '<button type="button" 
                                                class="btn btn-danger btn-sm update-status-btn" 
                                                data-id="' . $model->id . '" 
                                                data-status="rejected">
                                            Reject
                                        </button>';
                                },
                            ],
                        ],
                    ],
                ]); ?>
            <?php else: ?>
                <div class="alert alert-info">No contract renewals found for this company.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-3">
        <?= Html::a('Back to Dashboard', ['site/dashboard'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<?php
// Include the same JavaScript to handle approve/reject actions
$updateStatusUrl = Url::to(['site/update-renewal-status']);
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
// Simple click handler
$('.update-status-btn').click(function(e) {
    e.preventDefault();
    
    var btn = $(this);
    var id = btn.data('id');
    var status = btn.data('status');
    
    var confirmMessage = status === 'approved' 
        ? 'Are you sure you want to approve this renewal? This will also update the company\'s contract end date.'
        : 'Are you sure you want to reject this renewal request?';
    
    if (confirm(confirmMessage)) {
        $.ajax({
            url: '$updateStatusUrl',
            type: 'POST',
            dataType: 'json',
            data: {
                id: id,
                status: status,
                _csrf: '$csrfToken'
            },
            beforeSend: function() {
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update status');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {
                    error: error,
                    status: status,
                    response: xhr.responseText
                });
                alert('Error occurred: ' + error);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(status === 'approved' ? 'Approve' : 'Reject');
            }
        });
    }
});
JS;
$this->registerJs($js);
?> 