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
                    'label' => 'Time Taken',
                    'value' => function ($model) {
                        if ($model->status === Ticket::STATUS_CLOSED && $model->time_taken !== null) {
                            $days = floor($model->time_taken / 1440);
                            $hours = floor(($model->time_taken % 1440) / 60);
                            $minutes = $model->time_taken % 60;
                            return sprintf('%d days, %d hours, %d minutes', $days, $hours, $minutes);
                        } else {
                            return 'Reviewing';
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

<style>
/* Orange-themed Ticket View Styles */
.ticket-view {
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
    background-color: #FFFFFF;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.ticket-view h1 {
    color: #FF6F00;
    text-align: center;
    margin-bottom: 20px;
    font-weight: bold;
}

/* GridView styles */
.grid-view {
    background-color: #FFFFFF;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.grid-view table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

.grid-view th {
    background-color: #FF9800;
    color: #FFFFFF;
    border-bottom: 2px solid #F57C00;
    padding: 12px;
    font-weight: bold;
}

.grid-view td {
    border-bottom: 1px solid #FFE0B2;
    padding: 10px;
}

.grid-view tr:nth-child(even) {
    background-color: #FFF8E1;
}

.grid-view tr:hover {
    background-color: #FFE0B2;
}

/* Button styles */
.btn-warning {
    background-color: #FFB74D;
    border-color: #FFB74D;
    color: #FFFFFF;
}

.btn-warning:hover, .btn-warning:focus {
    background-color: #F57C00;
    border-color: #F57C00;
    color: #FFFFFF;
}

/* Alert styles */
.alert-info {
    background-color: #FFF3E0;
    border-color: #FFE0B2;
    color: #F57C00;
}

.alert-link {
    color: #FF5722;
    font-weight: bold;
}

.alert-link:hover {
    color: #E64A19;
}

/* Status colors */
.status-open { color: #4CAF50; font-weight: bold; }
.status-closed { color: #F44336; font-weight: bold; }
.status-pending { color: #FFC107; font-weight: bold; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .ticket-view {
        max-width: 100%;
        margin: 10px;
    }
    
    .grid-view {
        overflow-x: auto;
    }
}