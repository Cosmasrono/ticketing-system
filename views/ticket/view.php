<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use app\models\Ticket;  // Add this line

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
                [
                    'label' => 'Time',
                    'value' => function ($model) {
                        if ($model->status === Ticket::STATUS_CLOSED) {
                            return '<span class="time-spent" data-ticket-id="' . $model->id . '">Loading...</span>';
                        } else {
                            return '<span class="remaining-time" data-seconds="' . $model->getRemainingTimeInSeconds() . '"></span>';
                        }
                    },
                    'format' => 'raw',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{action}',
                    'buttons' => [
                        'action' => function ($url, $model, $key) {
                            if ($model->status === Ticket::STATUS_CLOSED) {
                                return Html::button('Reopen', [
                                    'class' => 'btn btn-warning btn-sm reopen-ticket',
                                    'data-ticket-id' => $model->id,
                                ]);
                            }
                            return '';  // Return empty string for non-closed tickets
                        },
                    ],
                    'header' => 'Action',
                ],
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for reopen ticket buttons
    document.querySelectorAll('.reopen-ticket').forEach(function(button) {
        button.addEventListener('click', function() {
            if (this.disabled) {
                return;  // Do nothing if the button is disabled
            }
            if (confirm('Are you sure you want to reopen this ticket? It will be set to pending status.')) {
                let ticketId = this.getAttribute('data-ticket-id');
                fetch('<?= Url::to(['ticket/reopen']) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                    },
                    body: 'id=' + ticketId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Ticket reopened successfully. ' + data.message);
                        location.reload();
                    } else {
                        console.error('Failed to reopen ticket:', data.message);
                        alert('Failed to reopen the ticket: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });

    // Load time spent for closed tickets
    document.querySelectorAll('.time-spent').forEach(function(span) {
        let ticketId = span.getAttribute('data-ticket-id');
        fetch('<?= Url::to(['ticket/get-time-spent']) ?>?id=' + ticketId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    span.textContent = data.timeSpent;
                } else {
                    span.textContent = 'Error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                span.textContent = 'Error';
            });
    });
});
</script>