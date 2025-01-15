<?php
use app\models\ContractRenewal;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
use app\models\Ticket; // Add this line to import the Ticket model
use yii\helpers\Url;
use app\models\User; // Add this line to import the User class
use app\models\Client; // Add this line to import the Client model

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $ticketCounts array */

$dataProvider->query->orderBy(['id' => SORT_DESC]);
$dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

$this->title = 'Iansoft Ticket Management System';
$this->params['breadcrumbs'][] = $this->title;

// Fetch clients from the database
$clients = Client::find()->select(['company_name', 'company_email'])->all();

// Count the number of clients
$clientCount = Client::find()->count();
?>




<!-- Add this button in a suitable location in your admin view -->
 

    
<div class="row mb-4">
        <div class="col">
            <?= Html::a('Create New User', ['site/create-user'], [
                'class' => 'btn btn-success',
                'style' => 'margin-right: 10px;'
            ]) ?>
            
           
        </div>

<div class="row mb-2">
    <div class="col text-end">
        <?= Html::a('View Dashboard', ['site/dashboard'], ['class' => 'btn btn-info me-2']) ?>
  <!-- company create button -->
  <?= Html::a('Create users', '#', [
    'class' => 'btn btn-primary',
    'id' => 'create-user-btn',
    'data-bs-toggle' => 'modal',
    'data-bs-target' => '#roleSelectionModal'
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
            ['title' => 'Reopen Tickets', 'count' => $ticketCounts['reopen'] ?? 0, 'bg' => 'info'],
            //  reassigned tickets
            ['title' => 'Reassigned Tickets', 'count' => $ticketCounts['reassigned'] ?? 0, 'bg' => 'warning'],
            // escalated tickets
            ['title' => 'Escalated Tickets', 'count' => $ticketCounts['escalated'] ?? 0, 'bg' => 'warning'],
            // deleted tickets
            // ['title' => 'Deleted Tickets', 'count' => $ticketCounts['deleted'] ?? 0, 'bg' => 'danger'],
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
            [
                'attribute' => 'module',
                'value' => function ($model) {
                    Yii::error("Module value: " . var_export($model->module, true)); // Debug line
                    return empty($model->module) ? 'MOBILE' : $model->module;
                }
            ],
            [
                'attribute' => 'issue',
                'value' => function ($model) {
                    Yii::error("Issue value: " . var_export($model->issue, true)); // Debug line
                    return empty($model->issue) ? 'App Crashes' : $model->issue;
                }
            ],
            'description',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return ucfirst($model->status ?: 'Not Set');
                }
            ],
            [
                'attribute' => 'company_name',
                'value' => function ($model) {
                    // Try to get company name from the user who created the ticket
                    if ($model->created_by) {
                        $user = User::findOne($model->created_by);
                        if ($user && !empty($user->company_name)) {
                            return $user->company_name;
                        }
                    }
                    
                    // If no company name found in user, try ticket's company_name
                    if (!empty($model->company_name)) {
                        return $model->company_name;
                    }

                    // If no company name is found, return "Not Set"
                    return 'Not Set';
                }
                
            ],
            [
                'attribute' => 'company_email',
                'value' => function ($model) {
                    return $model->company_email ?: 'Not Set';
                }
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Created At (EAT)',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                }
            ],
            [
                'attribute' => 'assigned_to',
                'label' => 'Assigned Developer',
                'value' => function ($model) {
                    return $model->assignedTo ? $model->assignedTo->username : 'Not Assigned';
                }
            ],
            [
                'attribute' => 'time_taken',
                'value' => function ($model) {
                    if ($model->status === 'closed' && $model->created_at && $model->closed_at) {
                        $created = new DateTime($model->created_at);
                        $closed = new DateTime($model->closed_at);
                        $interval = $created->diff($closed);
                        
                        if ($interval->d > 0) {
                            return $interval->format('%d days, %h hrs');
                        } elseif ($interval->h > 0) {
                            return $interval->format('%h hrs, %i mins');
                        } else {
                            return $interval->format('%i minutes');
                        }
                    }
                    return 'Still Open';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'white-space: normal; word-wrap: break-word;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
    'template' => '<div class="btn-group action-buttons">{approve} {assign} {cancel}</div>',
    'buttons' => [
        'approve' => function ($url, $model, $key) {
            $isDisabled = $model->status === Ticket::STATUS_APPROVED ||
                         $model->status === Ticket::STATUS_CANCELLED ||
                         $model->status === Ticket::STATUS_ESCALATED ||
                         $model->status === Ticket::STATUS_CLOSED ||
                         $model->status === Ticket::STATUS_REASSIGNED;
                        //  disable assign button if the ticket is already assigned to the current user
            
            $tooltipText = '';
            if ($model->status === Ticket::STATUS_CANCELLED) {
                $tooltipText = 'Cannot approve cancelled ticket';
            } elseif ($model->status === Ticket::STATUS_APPROVED) {
                $tooltipText = 'Ticket already approved';
            } elseif ($model->status === Ticket::STATUS_ESCALATED) {
                $tooltipText = 'Cannot approve escalated ticket';
            } elseif ($model->status === Ticket::STATUS_CLOSED) {
                $tooltipText = 'Cannot approve closed ticket';
            }

            return Html::button('Approve', [
                'class' => 'btn btn-success btn-sm' . ($isDisabled ? ' disabled' : ''),
                'onclick' => !$isDisabled ? "approveTicket({$model->id})" : 'return false;',
                'data-id' => $model->id,
                'title' => $tooltipText ?: 'Approve ticket',
            ]);
        },
                  'assign' => function ($url, $model, $key) {
                        $isEscalated = $model->status === Ticket::STATUS_ESCALATED;
                        $isAlreadyAssigned = $model->assigned_to !== null && !$isEscalated;
                        $isCancelled = $model->status === Ticket::STATUS_CANCELLED;
                        $isClosed = $model->status === Ticket::STATUS_CLOSED;
                        
                        // Only allow reassignment if status is escalated
                        $canReassign = $isEscalated;
                        
                        // Check if button should be disabled
                        $isDisabled = (!$canReassign && $isAlreadyAssigned) || $isCancelled || $isClosed;
                        
                        // Determine tooltip text
                        $tooltipText = '';
                        if ($isCancelled) {
                            $tooltipText = 'Cannot assign cancelled ticket';
                        } elseif ($isAlreadyAssigned && !$canReassign) {
                            $tooltipText = 'Can only reassign escalated tickets';
                        } elseif ($isClosed) {
                            $tooltipText = 'Cannot assign closed ticket';
                        }

                        // Set button text based on status
                        $buttonText = $canReassign ? 'Reassign' : ($isAlreadyAssigned ? 'Assigned' : 'Assign');
                        
                        return Html::a($buttonText, ['ticket/assign', 'id' => $model->id], [
                            'class' => 'btn btn-primary btn-sm assign-button' . ($isDisabled ? ' disabled' : ''),
                            'title' => $tooltipText ?: ($buttonText . ' to Developer'),
                            'data-pjax' => '0',
                            'onclick' => (!$isDisabled) ? new JsExpression("
                                function(event) {
                                    event.preventDefault();
                                    assignTicket({$model->id});
                                }
                            ") : 'return false;',
                            'data-id' => $model->id,
                        ]);
                    },
                    'cancel' => function ($url, $model, $key) {
                        $isDisabled = $model->status === Ticket::STATUS_CANCELLED || 
                                     $model->status === Ticket::STATUS_APPROVED ||
                                     $model->status === Ticket::STATUS_CLOSED ||
                                     $model->status === Ticket::STATUS_REASSIGNED;
                        
                        $tooltipText = '';
                        if ($model->status === Ticket::STATUS_CANCELLED) {
                            $tooltipText = 'Ticket already cancelled';
                        } elseif ($model->status === Ticket::STATUS_APPROVED) {
                            $tooltipText = 'Cannot cancel approved ticket';
                        } elseif ($model->status === Ticket::STATUS_CLOSED) {
                            $tooltipText = 'Cannot cancel closed ticket';
                        } elseif ($model->status === Ticket::STATUS_REASSIGNED) {
                            $tooltipText = 'Cannot cancel reassigned ticket';
                        }

                        return Html::button('Cancel', [
                            'class' => 'btn btn-danger btn-sm' . ($isDisabled ? ' disabled' : ''),
                            'onclick' => $isDisabled ? 'return false;' : "cancelTicket(this)",
                            'data-id' => $model->id,
                            'title' => $tooltipText ?: 'Cancel ticket',
                        ]);
                    },

                    // add admin  to close the ticket 

                  'close' => function ($url, $model, $key) {
    if ($model->status !== 'closed') {
        return Html::button('<i class="fas fa-times-circle"></i> Close', [
            'class' => 'btn btn-warning btn-xs close-ticket',
            'data-id' => $model->id,
            'type' => 'button',
            'title' => 'Close this ticket'
        ]);
    }
    return '<span class="badge bg-secondary">Closed</span>';
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
function approveTicket(ticketId) {
    if (!ticketId) {
        alert('Ticket ID is missing!');
        return;
    }

    if (confirm('Are you sure you want to approve this ticket?')) {
        $.ajax({
            url: '<?= \yii\helpers\Url::to(['/ticket/approve']) ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                id: ticketId,
                _csrf: '<?= Yii::$app->request->csrfToken ?>'
            },
            beforeSend: function() {
                showLoading();
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    location.reload();
                } else {
                    console.error('Server response:', response);
                    alert('Failed to approve ticket: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Ajax error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('An error occurred while processing your request. Check console for details.');
            }
        });
    }
}


function assignTicket(ticketId) {
    if (!ticketId) {
        alert('Ticket ID is required');
        return;
    }

    // Load the assignment form in a modal
    $.get('<?= \yii\helpers\Url::to(['/ticket/assign', 'id' => '']) ?>' + ticketId, function(html) {
        $('#assignModal .modal-body').html(html);
        $('#assignModal').modal('show');
        
        // Remove any existing event handlers
        $('#assign-form').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var button = $('[data-id="' + ticketId + '"].assign-button'); // Get the assign button

            $.ajax({
                url: '<?= \yii\helpers\Url::to(['/ticket/assign']) ?>?id=' + ticketId,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: function() {
                    // Disable the button during the request
                    button.prop('disabled', true);
                },
                success: function(response) {
                    $('#assignModal').modal('hide');
                    if (response.success) {
                        // Disable the assign button permanently
                        button.addClass('disabled')
                              .prop('disabled', true)
                              .attr('title', 'Ticket already assigned')
                              .off('click')
                              .text('Assigned');

                        // Optional: Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: 'Ticket has been assigned successfully',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Reload to update all statuses
                        });
                    } else {
                        // Re-enable the button if assignment failed
                        button.prop('disabled', false);
                        alert(response.message || 'Failed to assign ticket');
                    }
                },
                error: function() {
                    // Re-enable the button on error
                    button.prop('disabled', false);
                    $('#assignModal').modal('hide');
                    alert('An error occurred while assigning the ticket. Please try again.');
                }
            });
            
            return false;
        });
    });
}

        function updateAdminMessage(message, type) {
    var messageArea = $('#admin-message-area');
    messageArea.removeClass('alert-success alert-danger alert-info')
               .addClass('alert-' + (type === 'error' ? 'danger' : (type === 'success' ? 'success' : 'info')))
               .text(message)
               .show();

    // Optionally scroll to the message area
    $('html, body').animate({
        scrollTop: messageArea.offset().top - 100
    }, 200);
}

function cancelTicket(button) {
    var id = $(button).data('id');
    
    if (confirm('Are you sure you want to cancel this ticket?')) {
        $.ajax({
            url: '<?= Url::to(["/ticket/cancel"]) ?>',
            type: 'POST',
            data: {
                id: id,
                // Use the correct CSRF token from Yii
                '<?= Yii::$app->request->csrfParam ?>': '<?= Yii::$app->request->csrfToken ?>'
            },
            dataType: 'json',
            beforeSend: function() {
                showLoading();
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Failed to cancel ticket',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while cancelling the ticket.',
                    icon: 'error'
                });
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

function closeTicket(ticketId) {
    if (confirm('Are you sure you want to close this ticket?')) {
        $.ajax({
            url: '<?= \yii\helpers\Url::to(['ticket/close']) ?>',
            type: 'POST',
            data: {
                id: ticketId,
                _csrf: '<?= Yii::$app->request->csrfToken ?>'
            },
            dataType: 'json',
            beforeSend: function() {
                showLoading();
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Ticket closed successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Reload to update the ticket list
                    });
                } else {
                    alert('Failed to close ticket: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Ajax error:', { status: status, error: error });
                alert('An error occurred while processing your request. Check console for details.');
            }
        });
    }
}


$('#assign-form').on('beforeSubmit', function(e) {
    e.preventDefault();
    
    var form = $(this);

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function(response) {
            // Close modal first
            $('#assignModal').modal('hide');
            
            // Redirect to admin page
            window.location.href = '<?= \yii\helpers\Url::to(['/site/admin']) ?>';
        },
        complete: function() {
            // Prevent the form from submitting normally
            return false;
        }
    });
    
    // Prevent default form submission
    return false;
});
</script>
 


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

.cancelled-row .btn-approve,
.cancelled-row .btn-assign,
.cancelled-row .btn-close {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary.disabled {
    opacity: 0.65;
    pointer-events: none !important;
    cursor: not-allowed !important;
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.btn.disabled, 
.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed !important;
    pointer-events: none;
}

.assign-button.disabled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    opacity: 0.65;
}

/* Optional: Add transition for smooth state change */
.assign-button {
    transition: all 0.3s ease;
}

/* Ticket Icon Styles */
.ticket-icon-container {
    position: fixed;
    top: 20px;
    left: 20px;
    width: 100px;
    height: 60px;
    pointer-events: none;
    z-index: 1;
}

.ticket-icon {
    width: 100%;
    height: 100%;
    position: relative;
    animation: floatTicket 6s ease-in-out infinite;
}

.ticket-body {
    background: #ff8c00;
    width: 100%;
    height: 100%;
    border-radius: 8px;
    position: relative;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.ticket-hole {
    position: absolute;
    width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    left: 10px;
}

.ticket-hole:nth-child(1) { top: 10px; }
.ticket-hole:nth-child(2) { top: 50%; transform: translateY(-50%); }
.ticket-hole:nth-child(3) { bottom: 10px; }

.ticket-text {
    position: absolute;
    right: 10px;
    height: 8px;
    background: rgba(255,255,255,0.7);
    border-radius: 4px;
}

.ticket-text:nth-child(4) {
    width: 40%;
    top: 15px;
}

.ticket-text:nth-child(5) {
    width: 60%;
    bottom: 15px;
}

@keyframes floatTicket {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

/* Add a subtle glow effect */
.ticket-body::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.2), transparent);
    border-radius: 8px;
    animation: glowPulse 2s ease-in-out infinite;
}

@keyframes glowPulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 0.8; }
}
</style>

<!-- Add this modal -->
<div class="modal fade" id="roleSelectionModal" tabindex="-1" aria-labelledby="roleSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleSelectionModalLabel">Select User Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-3">
                    <?= Html::a('Company User', ['site/create-company'], [
                        'class' => 'btn btn-outline-primary btn-lg'
                    ]) ?>
                    <?= Html::a('Developer', ['site/create-developer'], [
                        'class' => 'btn btn-outline-success btn-lg'
                    ]) ?>
                    <?= Html::a('Admin', ['site/create-admin'], [
                        'class' => 'btn btn-outline-danger btn-lg'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    // Optional: Add animation when hovering over buttons
    $('.modal-body .btn').hover(
        function() { $(this).removeClass('btn-outline-*').addClass('btn-*'); },
        function() { $(this).addClass('btn-outline-*').removeClass('btn-*'); }
    );
JS;
$this->registerJs($js);
?>

<?php
$js = <<<JS
    $('.role-select-btn').on('click', function() {
        var selectedRole = $(this).data('role');
        if (selectedRole === 'company') {
            window.location.href = '/site/create-company';
        } else {
            // For admin/developer, redirect without showing modules
            window.location.href = '/site/create-' + selectedRole;
        }
    });

    // In create-company view
    $(document).ready(function() {
        if ($('#role-select').val() === 'company') {
            $('#company-fields').show();
            $('.modules-section').show();
        } else {
            $('#company-fields').hide();
            $('.modules-section').hide();
        }
    });
JS;
$this->registerJs($js);
?>

<!-- Add this section to your admin dashboard -->
    <!-- Add this section to your dashboard -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Contract Renewals</h3>
        </div>
        <div class="card-body">
            <?php
            $renewals = ContractRenewal::find()
                ->with(['company', 'requestedBy'])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
            ?>

            <?php if (!empty($renewals)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Requested By</th>
                                <th>Current End Date</th>
                                <th>Extension</th>
                                <th>New End Date</th>
                                <th>Status</th>
                                <th>Requested On</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($renewals as $renewal): ?>
                                <tr>
                                    <td><?= Html::encode($renewal->company->company_name) ?></td>
                                    <td><?= Html::encode($renewal->requestedBy->username) ?></td>
                                    <td><?= Yii::$app->formatter->asDate($renewal->current_end_date) ?></td>
                                    <td><?= $renewal->extension_period ?> months</td>
                                    <td><?= Yii::$app->formatter->asDate($renewal->new_end_date) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $renewal->renewal_status == 'pending' ? 'warning' : 
                                            ($renewal->renewal_status == 'approved' ? 'success' : 'danger') ?>">
                                            <?= ucfirst($renewal->renewal_status) ?>
                                        </span>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDatetime($renewal->created_at) ?></td>
                                    <td><?= Html::encode($renewal->notes) ?></td>
                                    <td>
                                        <?php if ($renewal->renewal_status === 'pending'): ?>
                                            <div class="btn-group">
                                                <?= Html::a('Approve', 
                                                    ['approve-renewal', 'id' => $renewal->id], 
                                                    [
                                                        'class' => 'btn btn-success btn-sm',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to approve this renewal?',
                                                            'method' => 'post',
                                                        ],
                                                    ]
                                                ) ?>
                                                <?= Html::a('Reject', 
                                                    ['reject-renewal', 'id' => $renewal->id], 
                                                    [
                                                        'class' => 'btn btn-danger btn-sm ms-1',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to reject this renewal?',
                                                            'method' => 'post',
                                                        ],
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No actions available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No contract renewal requests found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.badge {
    font-size: 0.875rem;
}
</style>

<div class="client-list">
    <h2>Client List</h2>
    <p>Total Clients: <?= htmlspecialchars($clientCount) ?></p>
    <table class="table">
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client->company_name) ?></td>
                    <td><?= htmlspecialchars($client->company_email) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

 


