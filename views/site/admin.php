<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
use app\models\Ticket; // Add this line to import the Ticket model

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $ticketCounts array */

$this->title = 'Iansoft Ticket Management System';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row mb-2">
    <div class="col text-end">
        <?= Html::a('New Client', ['/site/create-client'], [
            'class' => 'btn btn-sm btn-success',
            'style' => 'font-size: 0.8rem; padding: 0.25rem 0.5rem;'
        ]) ?>
    </div>
</div>
<div class="admin-index container mt-5">
    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <!-- Ticket Count Cards -->
     <!-- just lik any other button -->
     <div class="row text-center mb-4">
    
   
    <div class="row text-center mb-4">



    
        <?php
        $statuses = [
            ['title' => 'Pending Tickets', 'count' => $ticketCounts['pending'] ?? 0, 'bg' => 'primary'],
            ['title' => 'Approved Tickets', 'count' => $ticketCounts['approved'] ?? 0, 'bg' => 'success'],
            ['title' => 'Cancelled Tickets', 'count' => $ticketCounts['cancelled'] ?? 0, 'bg' => 'danger'],
            ['title' => 'Assigned Tickets', 'count' => $ticketCounts['assigned'] ?? 0, 'bg' => 'info'],
            ['title' => 'Not Assigned Tickets', 'count' => $ticketCounts['notAssigned'] ?? 0, 'bg' => 'warning'],
            ['title' => 'Closed Tickets', 'count' => $ticketCounts['closed'] ?? 0, 'bg' => 'secondary'],
            // escalated tickets
            ['title' => 'Escalated Tickets', 'count' => $ticketCounts['escalated'] ?? 0, 'bg' => 'warning'],
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
            'module',
            'issue',
         
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
                    return $model->timeTaken;
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'white-space: normal; word-wrap: break-word;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '<div class="btn-group action-buttons">{approve} {assign} {cancel} {reopen}</div>',
                'buttons' => [
                   'approve' => function ($url, $model, $key) {
                        $isDisabled = $model->status === Ticket::STATUS_APPROVED || $model->status === Ticket::STATUS_ESCALATED;
                        return Html::a('Approve', '#', [
                            'class' => 'btn btn-success btn-sm' . ($isDisabled ? ' disabled' : ''),
                            'title' => 'Approve Ticket',
                            'onclick' => $isDisabled ? 'return false;' : new JsExpression("approveTicket($(this), {$model->id})"),
                            'data-id' => $model->id,
                        ]);
                    },
                   'assign' => function ($url, $model, $key) {
                        $isEscalated = $model->status === Ticket::STATUS_ESCALATED;
                        $isDisabled = !$isEscalated && $model->assigned_to !== null;
                        $buttonText = $isEscalated ? 'Reassign' : 'Assign';
                        $assignUrl = \yii\helpers\Url::to(['ticket/assign', 'id' => $model->id]);
                        
                        return Html::a($buttonText, $assignUrl, [
                            'class' => 'btn btn-primary assign-button' . ($isDisabled ? ' disabled' : ''),
                            'title' => $buttonText . ' to Dev',
                            'data-id' => $model->id,
                            'data-method' => 'get',
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
                    'reopen' => function ($url, $model, $key) {
                        if ($model->status === Ticket::STATUS_CLOSED) {
                            return Html::a('Reopen', '#', [
                                'class' => 'btn btn-warning btn-sm',
                                'onclick' => new JsExpression("reopenTicket({$model->id}); return false;"),
                                'data-id' => $model->id,
                            ]);
                        }
                        return '';
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
            _csrf: '<?= Yii::$app->request->csrfToken ?>',
            closed_at: new Date().toISOString()
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                location.reload(); // Reload the page to show updated data
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


function assignTicket(button, ticketId, currentDeveloperId) {
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/assign']) ?>',
        type: 'POST',
        data: {
            id: ticketId,
            current_developer_id: currentDeveloperId,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        success: function(response) {
            if (response.success) {
                button.addClass('disabled').attr('onclick', 'return false;');
                alert('Ticket successfully ' + (currentDeveloperId ? 'reassigned' : 'assigned') + ' to ' + response.developerName);
            } else {
                alert('Failed to assign the ticket: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error assigning ticket:', textStatus, errorThrown);
            alert('Error assigning ticket: ' + errorThrown);
        }
    });
}
function cancelTicket(button) {
    var id = $(button).data('id');
    var isDisabled = $(button).hasClass('disabled');
    if (!isDisabled) {
        $.ajax({
            url: '<?= \yii\helpers\Url::to(['/ticket/cancel']) ?>',
            type: 'POST',
            data: {
                id: id,
                _csrf: '<?= Yii::$app->request->csrfToken ?>',
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload the page to show updated data
                } else {
                    alert('Failed to cancel the ticket: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error canceling ticket:', textStatus, errorThrown);
                alert('Error canceling ticket: ' + errorThrown);
            }
        });
    }
}

function reopenTicket(ticketId) {
    if (confirm('Are you sure you want to reopen this ticket?')) {
        $.ajax({
            url: '<?= \yii\helpers\Url::to(['ticket/reopen']) ?>',
            type: 'POST',
            data: {
                id: ticketId,
                _csrf: '<?= Yii::$app->request->csrfToken ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error reopening ticket:', textStatus, errorThrown);
                alert('An error occurred while trying to reopen the ticket: ' + errorThrown);
            }
        });
    }
}
</script>
/* General Body Styling */


<style>
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
    background-color: #ffe4b5; /* Light orange background */
    border: none; /* Remove borders */
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
    background-color: #ff8c00; /* Orange button */
    color: white; /* White text for contrast */
    border: none;
}
.btn:hover {
    background-color: #ffa500; /* Slightly darker orange on hover */
    opacity: 0.9;
}

/* Small Button Styling */
.btn-small {
    padding: 0.25rem 0.5rem; /* Smaller padding */
    font-size: 0.8rem; /* Smaller font size */
    border-radius: 3px; /* Slightly rounded corners */
    background-color: #ffcc80; /* Light orange shade for smaller buttons */
}

/* Loading Spinner Styling */
.spinner-border {
    width: 3rem;
    height: 3rem;
}
/* Time Taken Column Styling */
.grid-view td:nth-child(8) {
    font-weight: bold;
    color: #ff8c00; /* Bright orange for the time taken column */
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
    background-color: #ff8c00; /* Orange for the table header */
    color: #ffffff;
}
.table tbody tr:hover {
    background-color: #ffe4b5; /* Light orange on row hover */
}

/* Ensure GridView is responsive */
.grid-view {
    width: 100%; /* Full width for GridView */
    overflow-x: auto; /* Allow horizontal scrolling if necessary */
}

/* Custom Styles for Create Client Button */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Custom Styles for New Client Button */
.btn-outline-success {
    color: #28a745;
    border-color: #28a745;
    transition: all 0.2s ease;
}

.btn-outline-success:hover {
    color: #fff;
    background-color: #28a745;
    border-color: #28a745;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}















