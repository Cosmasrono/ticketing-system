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

    <!-- Ticket Statistics Cards -->
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
        <!-- closed tickets -->
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
                updateTicketCounts();
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

function updateTicketCounts() {
    // Optionally, implement AJAX to update the ticket counts dynamically
    // For example:
    /*
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/ticket/counts']) ?>',
        type: 'GET',
        success: function(response) {
            if(response.success){
                // Update each card's count
                $('.card-header').each(function(){
                    var header = $(this).text().trim();
                    var count = response.data[header.toLowerCase().replace(' ', '')] || 0;
                    $(this).next('.card-body').find('.card-title').text(count);
                });
            }
        }
    });
    */
}
</script>

<!-- Enhanced CSS Styling -->
<style>
/* Card Styling */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Table Styling */
.table th {
    background-color: #e9ecef;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05rem;
    color: #343a40;
}

.table td {
    vertical-align: middle;
    padding: 15px;
    color: #495057;
    font-size: 0.95rem;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.table-bordered th, .table-bordered td {
    border: 1px solid #dee2e6;
}

/* Button Styling */
a.btn {
    border-radius: 5px;
    padding: 8px 12px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

a.btn:hover {
    transform: translateY(-3px);
}

a.disabled {
    opacity: 0.6;
    pointer-events: none;
}

/* Loading Spinner */
#loading {
    margin-top: 20px;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    animation: spinner-grow 0.8s linear infinite;
}

@keyframes spinner-grow {
    0% {
        transform: scale(0.5);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(0.5);
    }
}

/* Grid Spacing */
.row {
    margin-bottom: 30px;
}

.card-title {
    font-size: 1.5rem;
    font-weight: bold;
}

/* Global Text Styling */
h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 40px;
    color: #343a40;
}

.container {
    padding: 40px 0;
}

.text-center {
    text-align: center !important;
}

.text-white {
    color: #fff !important;
}

/* Adjust card hover effect on mobile */
@media (max-width: 768px) {
    .card:hover {
        transform: none;
        box-shadow: none;
    }
}
</style>
