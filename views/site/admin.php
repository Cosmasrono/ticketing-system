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
<div class="ticket-index container mt-5">
    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>
    <div class="row text-center mb-4">
        <!-- Pending Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-header">Pending Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['pending'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
        <!-- Approved Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-success h-100">
                <div class="card-header">Approved Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['approved'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
        <!-- Cancelled Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-header">Cancelled Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['cancelled'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
        <!-- Assigned Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-info h-100">
                <div class="card-header">Assigned Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['assigned'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
        <!-- Not Assigned Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-header">Not Assigned Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['notAssigned'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
        <!-- Closed Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-secondary h-100">
                <div class="card-header">Closed Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['closed'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
        <!-- Total Tickets -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card text-white bg-secondary h-100">
                <div class="card-header">Total Tickets</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title"><?= $ticketCounts['total'] ?? 0 ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading" style="display:none;" class="text-center mb-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
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
                'template' => '{approve} {assign} {cancel}',
                'buttons' => [
                    'approve' => function ($url, $model, $key) {
                        $isDisabled = $model->status === 'approved';
                        return Html::a('Approve', '#', [
                            'class' => 'btn btn-success' . ($isDisabled ? ' disabled' : ''),
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
                            'class' => 'btn btn-danger' . ($isDisabled ? ' disabled' : ''),
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

function cancelTicket(button) {
    var ticketId = button.data('id');
    if (!confirm('Are you sure you want to cancel this ticket?')) {
        return;
    }

    showLoading();
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/cancel']) ?>',
        type: 'POST',
        data: {
            id: ticketId,
            _csrf: '<?= Yii::$app->request->csrfToken ?>'
        },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                var row = button.closest('tr');
                row.find('td').eq(3).text('Cancelled');
                disableButtons(row);
            } else {
                alert('Failed to cancel the ticket: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            hideLoading();
            console.error('Error cancelling ticket:', textStatus, errorThrown);
            alert('Error cancelling ticket: ' + errorThrown);
        }
    });
}

function disableButtons(row) {
    row.find('a.btn').addClass('disabled').attr('disabled', true);
}

function updateTimeTaken(row, closedAt) {
    var createdAt = new Date(row.find('td').eq(5).text()); // Assuming created_at is the 6th column
    var closedDate = new Date(closedAt);
    var diff = Math.abs(closedDate - createdAt);
    var days = Math.floor(diff / (1000 * 60 * 60 * 24));
    var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    row.find('td').eq(7).text(days + ' days, ' + hours + ' hours, ' + minutes + ' minutes');
}
</script>

<!-- Enhanced Professional CSS Styling -->
<style>
/* General Body Styling */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    color: #343a40;
}

/* Card Styling */
.card {
    border-radius: 8px;
    transition: transform 0.2s;
}
.card:hover {
    transform: scale(1.05);
}

/* Button Styling */
.btn {
    font-weight: bold;
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
</style>
