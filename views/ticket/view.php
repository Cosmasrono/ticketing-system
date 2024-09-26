<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

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
                        if ($model->status === 'Closed') {
                            return '<span class="time-spent" data-ticket-id="' . $model->id . '">Loading...</span>';
                        } else {
                            return '<span class="remaining-time" data-seconds="' . $model->getRemainingTimeInSeconds() . '"></span>';
                        }
                    },
                    'format' => 'raw',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{close}',
                    'buttons' => [
                        'close' => function ($url, $model, $key) {
                            return Html::button('Close', [
                                'class' => 'btn btn-danger btn-sm close-ticket',
                                'data-ticket-id' => $model->id,
                            ]);
                        },
                    ],
                    'visibleButtons' => [
                        'close' => function ($model) {
                            return $model->status !== 'Closed';
                        },
                    ],
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
    function updateTimers() {
        document.querySelectorAll('.remaining-time').forEach(function(element) {
            let seconds = parseInt(element.getAttribute('data-seconds'));
            if (seconds > 0) {
                seconds--;
                element.setAttribute('data-seconds', seconds);
                let hours = Math.floor(seconds / 3600);
                let minutes = Math.floor((seconds % 3600) / 60);
                let secs = seconds % 60;
                element.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                element.textContent = '00:00:00';
            }
        });
    }

    function loadTimeSpent() {
        document.querySelectorAll('.time-spent').forEach(function(element) {
            let ticketId = element.getAttribute('data-ticket-id');
            fetch('<?= Url::to(['ticket/get-time-spent']) ?>?id=' + ticketId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        element.textContent = data.timeSpent;
                    } else {
                        element.textContent = 'Error';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    element.textContent = 'Error';
                });
        });
    }

    setInterval(updateTimers, 1000);
    updateTimers(); // Initial call to set the timers immediately
    loadTimeSpent(); // Load time spent for closed tickets

    // Add event listener for close buttons
    document.querySelectorAll('.close-ticket').forEach(function(button) {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to close this ticket?')) {
                let ticketId = this.getAttribute('data-ticket-id');
                fetch('<?= Url::to(['ticket/close']) ?>', {
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
                        // Update the status in the grid
                        let row = this.closest('tr');
                        let statusCell = row.querySelector('td:nth-child(3)');
                        statusCell.textContent = 'Closed';
                        // Update the time cell
                        let timeCell = row.querySelector('.remaining-time');
                        timeCell.className = 'time-spent';
                        timeCell.setAttribute('data-ticket-id', ticketId);
                        timeCell.textContent = 'Loading...';
                        loadTimeSpent();
                        // Hide the close button
                        this.style.display = 'none';
                    } else {
                        alert('Failed to close the ticket. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
});
</script>
