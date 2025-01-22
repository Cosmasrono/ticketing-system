<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use app\models\ContractRenewal;

$this->title = 'Help Desk Analytics Dashboard';

// Add this helper function at the top of your view file
function getStatusColor($status) {
    $colors = [
        'assigned' => 'bg-primary',
        'closed' => 'bg-success',
        'pending' => 'bg-warning',
        'urgent' => 'bg-danger',
        'reassigned' => 'bg-info',
        'approved' => 'bg-success',
        'cancelled' => 'bg-secondary'
    ];
    return $colors[strtolower($status)] ?? 'bg-secondary';
}

// Register jQuery if not already registered
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11');
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

// Register the JavaScript in POS_HEAD to ensure it's available before DOM elements
$this->registerJs("
    window.toggleUserStatus = function(userId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to change this user\'s status?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '" . Url::to(['/site/toggle-status']) . "',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: userId,
                        _csrf: yii.getCsrfToken()
                    },
                    beforeSend: function() {
                        // Clear any previous output
                        console.clear();
                    },
                    success: function(response) {
                        try {
                            if (response && response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(response.message || 'Unknown error');
                            }
                        } catch (e) {
                            console.error('Response parsing error:', e);
                            Swal.fire({
                                title: 'Error!',
                                text: e.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', {xhr, status, error});
                        let errorMessage = 'An error occurred while processing your request';
                        
                        try {
                            // Try to get response text if it exists
                            const response = xhr.responseText;
                            if (response) {
                                // Remove any debug output before JSON
                                const jsonStart = response.indexOf('{');
                                if (jsonStart >= 0) {
                                    const cleanJson = response.substring(jsonStart);
                                    const parsed = JSON.parse(cleanJson);
                                    if (parsed.message) {
                                        errorMessage = parsed.message;
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }

                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    };
", \yii\web\View::POS_HEAD);
?>

<style>
.dashboard-container {
    display: flex;
    min-height: 100vh;
}

.dashboard-sidebar {
    width: 250px;
    background: #2E4374;
    padding: 20px 0;
    height: 100vh;
    position: fixed;
}

.dashboard-content {
    flex: 1;
    margin-left: 250px;
    padding: 20px;
    background: #f8f9fc;
}

.dash-nav-item {
    display: flex;
    align-items: center;
    padding: 12px 25px;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    margin-bottom: 5px;
}

.dash-nav-item:hover {
    background: #FF6B35;
    color: white;
    text-decoration: none;
    border-left: 4px solid #FF9B35;
}

.dash-nav-item.active {
    background: #FF6B35;
    border-left: 4px solid #FF9B35;
}

.dash-nav-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.nav-section {
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 10px;
}

.nav-section-title {
    color: #FF9B35;
    padding: 10px 25px;
    font-size: 0.8em;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Content area styling */
.content-section {
    display: none;
}

.content-section.active {
    display: block;
}

@media (max-width: 768px) {
    .dashboard-sidebar {
        width: 70px;
    }
    .dash-nav-item span {
        display: none;
    }
    .dashboard-content {
        margin-left: 70px;
    }
    .nav-section-title {
        display: none;
    }
}
</style>

<div class="dashboard-container">
    <!-- Dashboard Sidebar -->
    <div class="dashboard-sidebar">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="#overview" class="dash-nav-item active" data-section="overview">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Client Management</div>
            <a href="#clients" class="dash-nav-item" data-section="clients">
                <i class="fas fa-users"></i>
                <span>Our Clients</span>
            </a>
            <a href="#contracts" class="dash-nav-item" data-section="contracts">
                <i class="fas fa-file-contract"></i>
                <span>Contracts</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Support</div>
            <a href="#tickets" class="dash-nav-item" data-section="tickets">
                <i class="fas fa-ticket-alt"></i>
                <span>Active Tickets</span>
            </a>
            <a href="#reports" class="dash-nav-item" data-section="reports">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <!-- Overview Section -->
        <div id="overview" class="content-section active">
            <h2>Dashboard Overview</h2>
            <!-- Add this section to your admin dashboard -->
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

            <div class="card mb-4">
                <div class="card-header">
                    <h3>Clients with Comments</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Comments</th>
                                    <th>Company Name</th>
                                    <th>Closed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <?php if (!empty($ticket->comments)): ?>
                                        <tr>
                                            <td><?= Html::encode($ticket->id) ?></td>
                                            <td>
                                                <span class="badge <?= getStatusColor($ticket->status) ?>">
                                                    <?= Html::encode($ticket->status) ?>
                                                </span>
                                            </td>
                                            <td><?= Yii::$app->formatter->asDatetime($ticket->created_at) ?></td>
                                            <td><?= Html::encode($ticket->comments) ?></td>
                                            <td>
                                                <?= $ticket->user ? Html::encode($ticket->user->company_name) : 
                                                    '<span class="text-muted">No Company</span>' ?>
                                            </td>
                                            <td><?= $ticket->closed_by ? Html::encode($ticket->closed_by) : 
                                                '<span class="text-muted">Not Closed</span>' ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (!array_filter($tickets, function($ticket) { return !empty($ticket->comments); })): ?>
                            <div class="alert alert-info">
                                No tickets with comments found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients Section -->
        <div id="clients" class="content-section">
            <h2>Our Clients</h2>
            <p>Total Clients: <?= Html::encode($clientCount) ?></p>
            <div>
                <?= Html::a('View Clients', ['client/index'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>

        <!-- Contracts Section -->
        <div id="contracts" class="content-section">
            <div class="section-header">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-gray-800">Contract Management</h2>
                        <p class="text-gray-600">Manage and track all client contracts</p>
                    </div>
                    <div>
                        <?= Html::a('<i class="fas fa-plus"></i> New Contract', ['contract/create'], [
                            'class' => 'btn btn-primary'
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- Contract Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Active Contracts</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $activeContractsCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Expiring Soon</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $expiringCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Renewed This Month</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $renewedCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-sync fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Expired</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $expiredCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Active Contracts</h6>
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" id="contractSearch" 
                               placeholder="Search contracts...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="contractsTable">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Contract Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts ?? [] as $contract): ?>
                                    <tr>
                                        <td><?= Html::encode($contract->client->company_name) ?></td>
                                        <td><?= Html::encode($contract->type) ?></td>
                                        <td><?= Yii::$app->formatter->asDate($contract->start_date) ?></td>
                                        <td><?= Yii::$app->formatter->asDate($contract->end_date) ?></td>
                                        <td>
                                            <span class="badge <?= $contract->getStatusBadgeClass() ?>">
                                                <?= Html::encode($contract->status) ?>
                                            </span>
                                        </td>
                                        <td><?= Yii::$app->formatter->asCurrency($contract->value) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['contract/view', 'id' => $contract->id], [
                                                    'class' => 'btn btn-sm btn-info',
                                                    'title' => 'View'
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['contract/update', 'id' => $contract->id], [
                                                    'class' => 'btn btn-sm btn-primary',
                                                    'title' => 'Edit'
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-sync"></i>', ['contract/renew', 'id' => $contract->id], [
                                                    'class' => 'btn btn-sm btn-success',
                                                    'title' => 'Renew'
                                                ]) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Section -->
        <div id="tickets" class="content-section">
            <!-- Ticket Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Active Tickets</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $activeTicketsCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Response</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingTicketsCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Urgent Tickets</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $urgentTicketsCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Resolved Today</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $resolvedTodayCount ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions and Filters -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="btn-group">
                        <?= Html::a('<i class="fas fa-plus"></i> New Ticket', ['ticket/create'], [
                            'class' => 'btn btn-primary'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-filter"></i> Filter', '#', [
                            'class' => 'btn btn-outline-primary',
                            'data-toggle' => 'modal',
                            'data-target' => '#filterModal'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-download"></i> Export', ['ticket/export'], [
                            'class' => 'btn btn-outline-primary'
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" id="ticketSearch" 
                               placeholder="Search tickets...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Priority Queue -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Priority Queue</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="priorityTable">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Client</th>
                                    <th>Issue</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Age</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($priorityTickets ?? [] as $ticket): ?>
                                    <tr class="<?= $ticket->getPriorityClass() ?>">
                                        <td>#<?= Html::encode($ticket->id) ?></td>
                                        <td><?= Html::encode($ticket->client->company_name) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($ticket->hasAttachments()): ?>
                                                    <i class="fas fa-paperclip text-muted mr-2"></i>
                                                <?php endif; ?>
                                                <?= Html::encode($ticket->title) ?>
                                            </div>
                                            <small class="text-muted"><?= Html::encode($ticket->getShortDescription()) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $ticket->getPriorityBadgeClass() ?>">
                                                <?= Html::encode($ticket->priority) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $ticket->getStatusBadgeClass() ?>">
                                                <?= Html::encode($ticket->status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($ticket->assigned_to): ?>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle mr-2">
                                                        <?= strtoupper(substr($ticket->assignedTo->username, 0, 1)) ?>
                                                    </div>
                                                    <?= Html::encode($ticket->assignedTo->username) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="<?= $ticket->getAgeClass() ?>">
                                                <?= $ticket->getAge() ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['ticket/view', 'id' => $ticket->id], [
                                                    'class' => 'btn btn-sm btn-info',
                                                    'title' => 'View'
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['ticket/update', 'id' => $ticket->id], [
                                                    'class' => 'btn btn-sm btn-primary',
                                                    'title' => 'Edit'
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-reply"></i>', ['ticket/respond', 'id' => $ticket->id], [
                                                    'class' => 'btn btn-sm btn-success',
                                                    'title' => 'Respond'
                                                ]) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Response Time Chart -->
            <div class="row mb-4">
                <div class="col-xl-8">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Response Time Analysis</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="responseTimeChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Issue Categories</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="severityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($recentActivities ?? [] as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="timeline-title">
                                        <?= Html::encode($activity->getTitle()) ?>
                                        <small class="text-muted"><?= Yii::$app->formatter->asRelativeTime($activity->created_at) ?></small>
                                    </h3>
                                    <p><?= Html::encode($activity->description) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Section -->
        <div id="reports" class="content-section">
            <h2>Reports</h2>
            <!-- Reports content -->
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Handle navigation clicks
    $('.dash-nav-item').click(function(e) {
        e.preventDefault();
        
        // Update active nav item
        $('.dash-nav-item').removeClass('active');
        $(this).addClass('active');
        
        // Show corresponding content
        const section = $(this).data('section');
        $('.content-section').removeClass('active');
        $('#' + section).addClass('active');
    });

    // Search functionality
    $('#contractSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#contractsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Initialize tooltips
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>

<?php
// Register required assets
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

// Chart configuration
$chartConfig = [
    'type' => 'doughnut',
    'data' => [
        'labels' => array_keys($ticketStatusData),
        'datasets' => [[
            'data' => array_values($ticketStatusData),
            'backgroundColor' => [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
            ],
            'hoverBackgroundColor' => [
                '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'
            ],
            'hoverBorderColor' => "rgba(234, 236, 244, 1)",
        ]]
    ],
    'options' => [
        'maintainAspectRatio' => false,
        'tooltips' => [
            'backgroundColor' => "rgb(255,255,255)",
            'bodyFontColor' => "#858796",
            'borderColor' => '#dddfeb',
            'borderWidth' => 1,
            'xPadding' => 15,
            'yPadding' => 15,
            'displayColors' => false,
            'caretPadding' => 10,
        ],
        'legend' => [
            'display' => true,
            'position' => 'bottom'
        ],
        'cutoutPercentage' => 80,
    ],
];

$this->registerJs("
    // Initialize Ticket Status Chart
    new Chart(document.getElementById('ticketStatusChart'), " . json_encode($chartConfig) . ");
");
?>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #FF6B35;
    border: 2px solid #fff;
}

.timeline-title {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.avatar-circle {
    width: 30px;
    height: 30px;
    background: #FF6B35;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
}

.priority-high {
    background-color: rgba(231, 74, 59, 0.1);
}

.priority-medium {
    background-color: rgba(246, 194, 62, 0.1);
}

.age-warning {
    color: #f6c23e;
}

.age-danger {
    color: #e74a3b;
}

/* Additional responsive styles */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin: 5px 0;
    }
}
</style>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Tickets</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add filter form here -->
            </div>
        </div>
    </div>
</div>

<?php
// Register necessary JavaScript
$this->registerJs("
    // Initialize charts
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseTimeCtx, {
        type: 'line',
        data: {
            labels: " . json_encode($responseTimeLabels) . ",
            datasets: [{
                label: 'Average Response Time (hours)',
                data: " . json_encode($responseTimeData) . ",
                borderColor: '#FF6B35',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Replace the existing chart initialization
    
    // Search functionality
    $('#ticketSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#priorityTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
");
?>

<!-- Commenting out the chart canvases and JavaScript initialization -->
<!-- 
<canvas id="severityChart"></canvas>
<canvas id="statusChart"></canvas>
<canvas id="responseTimeChart"></canvas>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Severity Chart
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($severityStats)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($severityStats)) ?>,
                backgroundColor: [
                    '#e74a3b', // Critical
                    '#f6c23e', // High
                    '#4e73df', // Medium
                    '#1cc88a'  // Low
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Tickets by Severity'
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($ticketStatusData)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($ticketStatusData)) ?>,
                backgroundColor: [
                    '#4e73df', // New
                    '#1cc88a', // In Progress
                    '#36b9cc', // Pending
                    '#f6c23e', // Resolved
                    '#e74a3b'  // Closed
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Tickets by Status'
                }
            }
        }
    });

    // Response Time Chart
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseTimeCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($responseTimeLabels) ?>,
            datasets: [{
                label: 'Average Response Time (hours)',
                data: <?= json_encode($responseTimeData) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
-->

<!-- You can add other content here if needed --> 