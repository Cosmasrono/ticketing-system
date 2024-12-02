<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

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
        <?php if (Yii::$app->user->identity->user_type === 'developer'): ?>
            <?= Html::button('Close Ticket', [
                'class' => 'btn btn-danger',
                'onclick' => 'initiateClose()',
                'id' => 'closeButton'
            ]) ?>

            <div id="closeCountdown" style="display: none;" class="alert alert-warning">
                <div class="text-center">
                    <h4>Ticket will be closed in:</h4>
                    <div class="timer-display">
                        <span id="countdown">60</span> seconds
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->screenshot)) {
                        return Html::img($model->getScreenshotUrl(), [
                            'class' => 'img-fluid', // Responsive image
                            'style' => 'max-width: 100%; height: auto;',
                            'alt' => 'Ticket Screenshot'
                        ]);
                    }
                    return '<span class="text-muted">No screenshot available</span>';
                },
            ],
        ],
    ]) ?>

    <?php if ($model->screenshot): ?>
        <div class="form-group">
            <label>Screenshot:</label>
            <img src="<?= $model->getScreenshotUrl() ?>" alt="Ticket Screenshot" class="img-fluid">
        </div>
    <?php endif; ?>

</div>

<?php
$script = <<<JS
function initiateClose() {
    if (!confirm('Are you sure you want to close this ticket?')) {
        return;
    }

    $('#closeCountdown').show();
    let timeLeft = 60; // 1 minute
    
    let countdownTimer = setInterval(function() {
        timeLeft--;
        $('#countdown').text(timeLeft);
        
        if (timeLeft <= 0) {
            clearInterval(countdownTimer);
            closeTicket();
        }
    }, 1000);
}

function closeTicket() {
    $.ajax({
        url: '/ticket/close',
        type: 'POST',
        data: { 
            id: {$model->id}
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('An error occurred');
        }
    });
}
JS;
$this->registerJs($script);
?>
