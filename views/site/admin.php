<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $ticketCounts array */

$this->title = 'Iansoft Ticket Management System';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-index container mt-5">
    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <!-- Ticket Count Cards -->
    <div class="row text-center mb-4">
        <?php
        $statuses = [
            ['title' => 'Pending Tickets', 'count' => $ticketCounts['pending'] ?? 0, 'bg' => 'primary'],
            ['title' => 'Approved Tickets', 'count' => $ticketCounts['approved'] ?? 0, 'bg' => 'success'],
            ['title' => 'Cancelled Tickets', 'count' => $ticketCounts['cancelled'] ?? 0, 'bg' => 'danger'],
            ['title' => 'Assigned Tickets', 'count' => $ticketCounts['assigned'] ?? 0, 'bg' => 'info'],
            ['title' => 'Not Assigned Tickets', 'count' => $ticketCounts['notAssigned'] ?? 0, 'bg' => 'warning'],
            ['title' => 'Closed Tickets', 'count' => $ticketCounts['closed'] ?? 0, 'bg' => 'secondary'],
        ];
        foreach ($statuses as $status): ?>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card text-white bg-<?= $status['bg'] ?> h-100">
                    <div class="card-header"><?= $status['title'] ?></div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h5 class="card-title"><?= $status['count'] ?></h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Total Tickets -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-4 col-md-6 col-sm-8">
            <div class="card text-white bg-dark">
                <div class="card-header">Total Tickets</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $ticketCounts['total'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets GridView -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            'id',
            'title',
            'description',
            'status',
            'company_email',
            'created_at:datetime',
            [
                'attribute' => 'developer.name',
                'label' => 'Assigned Developer',
                'value' => function ($model) {
                    return $model->developer ? $model->developer->name : 'Not Assigned';
                }
            ],
            [
                'label' => 'Time Taken',
                'value' => function ($model) {
                    if ($model->status === 'Closed' && $model->closed_at !== null) {
                        $createdAt = new DateTime($model->created_at);
                        $closedAt = new DateTime($model->closed_at);
                        $interval = $createdAt->diff($closedAt);
                        return $interval->format('%a days, %h hours, %i minutes');
                    }
                    return 'Not closed yet';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '<div class="btn-group action-buttons">{approve} {assign} {cancel}</div>',
                'buttons' => [
                    'approve' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'approved';
                        return Html::a('Approve', '#', [
                            'class' => 'btn btn-success btn-sm' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Approve Ticket',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("approveTicket($(this), {$model->id})"),
                            'data-id' => $model->id,
                            
                        ]);
                    },
                         'assign' => function ($url, $model, $key) {
                        $isDisabled = $model->assigned_to !== null;
                        return Html::a('Assign', '#', [
                            'class' => 'btn btn-primary' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Assign to Dev',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("assignTicket($(this), {$model->id})"),
                            'data-id' => $model->id,
                        ]);
                    },
                    'cancel' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'approved' || ($model->assigned_to !== null && $model->status !== 'pending');
                        return Html::a('Cancel', '#', [
                            'class' => 'btn btn-danger btn-sm' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Cancel Ticket',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("cancelTicket($(this))"),
                            'data-id' => $model->id,
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>

<!-- JavaScript Functions -->
<script>
function showLoading() {
    $('#loading').show();
}

function hideLoading() {
    $('#loading').hide();
}

function approveTicket(button, ticketId) {
    showLoading();
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/approve']) ?>',
        type: 'POST',
        data: {
            id: ticketId,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                var row = button.closest('tr');
                row.find('td').eq(3).text('Approved');
                disableButtons(row);
                updateTicketCounts();
            } else {
                alert('Failed to approve the ticket: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            hideLoading();
            console.error('Error approving ticket:', textStatus, errorThrown);
            alert('Error approving ticket: ' + errorThrown);
        }
    });
}

function assignTicket(button, ticketId) {
    window.location.href = '<?= \yii\helpers\Url::to(['/ticket/assign']) ?>' + '?id=' + ticketId;
}



function cancelTicket(button, ticketId) {
    console.log('Cancel function called with ticketId:', ticketId);
    if (confirm('Are you sure you want to cancel this ticket?')) {
        var formData = new FormData();
        formData.append('id', ticketId);
        formData.append('_csrf', '<?= Yii::$app->request->csrfToken ?>');

        console.log('FormData entries:');
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $.ajax({
            url: '<?= \yii\helpers\Url::to(['/ticket/cancel']) ?>',
            type: 'POST',
            data: formData,
            processData: false, // Important: Prevent jQuery from automatically transforming the data into a query string
            contentType: false, // Important: Prevent jQuery from overriding the Content-Type
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload or update the UI as needed
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error details:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                alert('Error cancelling ticket. Please check the console for details.');
            }
        });
    }
}


function disableButtons(row) {
    row.find('a.btn').addClass('disabled').attr('disabled', true);
}

function updateTimeTaken(row, closedAt) {
    var createdAt = new Date(row.find('td').eq(5).text());
    var closedDate = new Date(closedAt);
    var diff = Math.abs(closedDate - createdAt);
    var days = Math.floor(diff / (1000 * 60 * 60 * 24));
    var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    row.find('td').eq(7).text(days + ' days, ' + hours + ' hours, ' + minutes + ' minutes');
}
</script>
<style>
<!-- Enhanced Professional CSS Styling -->
/* General Body Styling */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    color: #343a40;
}

/* Container Styling */
.container {
    max-width: 100%; /* Allow full width */
    padding: 15px; /* Add padding for mobile */
}

/* Card Styling */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    width: 100%; /* Ensure cards take full width */
}
.card:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Button Styling */
.btn {
    font-weight: bold;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 5px;
    transition: background-color 0.2s;
}
.btn:hover {
    opacity: 0.9;
}

/* Small Button Styling */
.btn-small {
    padding: 0.25rem 0.5rem; /* Smaller padding */
    font-size: 0.8rem; /* Smaller font size */
    border-radius: 3px; /* Slightly rounded corners */
}

/* Loading Spinner Styling */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Time Taken Column Styling */
.grid-view td:nth-child(8) {
    font-weight: bold;
    color: #28a745;
}

/* Action buttons styling */
.action-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-width: 200px;
}

.action-buttons .btn {
    flex: 1;
    margin: 0 5px;
    white-space: nowrap;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-buttons .btn {
        margin: 5px 0;
    }
}

/* Additional Styles for GridView */
.table {
    border-radius: 10px;
    overflow: hidden;
    width: 100%; /* Ensure the table fits the screen */
}
.table thead th {
    background-color: #343a40;
    color: #ffffff;
}
.table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Ensure GridView is responsive */
.grid-view {
    width: 100%; /* Full width for GridView */
    overflow-x: auto; /* Allow horizontal scrolling if necessary */
}
