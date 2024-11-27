<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */

$this->title = 'Ticket #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Temporary debug code
echo '<div style="background: #f5f5f5; padding: 10px; margin: 10px 0; font-family: monospace;">';
echo "Ticket ID: " . $model->id . "<br>";
echo "Created By: " . $model->created_by . "<br>";

$creator = Yii::$app->db->createCommand("
    SELECT * FROM user WHERE id = :user_id
", [':user_id' => $model->created_by])->queryOne();

echo "Creator Data: <pre>" . print_r($creator, true) . "</pre>";
echo '</div>';
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
            [
                'attribute' => 'company_name',
                'label' => 'Company',
                'value' => function ($model) {
                    // Get creator's email from the debug data we know exists
                    $creator = Yii::$app->db->createCommand("
                        SELECT company_email 
                        FROM user 
                        WHERE id = :user_id
                    ", [':user_id' => $model->created_by])->queryOne();

                    if ($creator && !empty($creator['company_email'])) {
                        $email = $creator['company_email'];
                        // Extract domain and create company name
                        $domain = explode('@', $email)[1];
                        $companyName = ucwords(str_replace(['.com', '.org', '.net'], '', $domain));
                        
                        return Html::encode($companyName) . ' <small class="text-muted">(' . Html::encode($email) . ')</small>';
                    }

                    // Fallback to existing company_name if available
                    if (!empty($model->company_name)) {
                        return Html::encode($model->company_name);
                    }

                    return '<span class="text-muted">Not Available</span>';
                },
                'format' => 'raw',
            ],
            'module',
            'issue',
            'description:ntext',
            'status',
            'created_at:datetime',
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
