<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use app\models\ContractRenewal;
use app\models\User;
use app\models\Company;
use app\models\Ticket;
// grid
use yii\grid\GridView;
use yii\data\arrayDataProvider;
// use app\models\ContractRenewal;
use app\assets\SweetAlert2Asset;
use yii\web\View;

$this->title = 'Help Desk Analytics Dashboard';

// Add this helper function at the top of your view file
function getStatusColor($status)
{
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
    window.toggleUserStatus = function(userId, status) {
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
                        status: status,
                        _csrf: yii.getCsrfToken()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message || 'Unknown error', 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred while processing your request';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire('Error!', errorMessage, 'error');
                    }
                });
            }
        });
    };
", \yii\web\View::POS_HEAD);

// Helper function to convert status to text
function getStatusText($status) {
    return $status == User::STATUS_ACTIVE ? 'Active' : 'Inactive';
}

// Calculate ticket statistics using string comparisons
$openTickets = Ticket::find()->where(['status' => 'open'])->count();
$inProgressTickets = Ticket::find()->where(['status' => 'in progress'])->count();
$resolvedTickets = Ticket::find()->where(['status' => 'resolved'])->count();
$closedTickets = Ticket::find()->where(['status' => 'closed'])->count();
$totalTickets = $openTickets + $inProgressTickets + $resolvedTickets + $closedTickets;

// Modify the top developers query
$topDevelopers = Ticket::find()
    ->select([
        'users.id',
        'users.name AS developer_name',
        'users.company_name',
        'COUNT(*) as assigned_count',
        'COUNT(CASE WHEN ticket.status IN (\'closed\', \'resolved\') THEN 1 ELSE NULL END) as resolved_count'
    ])
    ->join('LEFT JOIN', 'users', 'ticket.assigned_to = users.id')
    ->where(['not', ['ticket.assigned_to' => null]])
    ->andWhere(['not', ['users.name' => null]])
    ->groupBy(['users.id', 'users.name', 'users.company_name'])
    ->orderBy(['assigned_count' => SORT_DESC])
    ->limit(5)
    ->asArray()
    ->all();
?>

<style>
        /* Container Styling */
        .container {
        max-width: 100%;
        padding: 0;
        overflow-x: hidden; /* Prevent horizontal scroll */
    }

    .finefooter{
        padding: 0px 60px;
        margin-bottom: -20px;
        
    }
</style>

<style>
    /* Fixed dash-Navigation Styles */
    .dashboard-dash-nav {
        justify-items: center;
        align-items: center;
        width: 100%;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 0 20px;
        z-index: 1000;
    }

    /* Add padding to content to prevent overlap */
    .dashboard-container {
        width: 100%;
        padding-top: 50px;

        /* Height of dash-nav + some spacing */
    }

    .dash-nav-trigger {
        display: none;
        padding: 15px 20px;
        background: #2E4374;
        color: white;
        cursor: pointer;
        align-items: center;
        gap: 10px;
    }

    .dash-nav-links {
        display: flex;
        gap: 20px;
        padding: 15px 0;
        margin: 0;
        list-style: none;
    }

    .dash-nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        color: #1a1a1a;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .dash-nav-link:hover,
    .dash-nav-link.active {
        background: #e0e7ff;
        color: #EA5626;
    }

    /* Client Dashboard Styles */
    .dashboard-container {
        width: 100%;
        padding: 0;
    }

    .dashboard-content {
        width: 100%;
        padding: 20px;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .client-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        padding: 20px;
        color: #2E4374;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Table Styles */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table {
        width: 100%;
        min-width: 1000px;
    }

    .table th {
        background: #f8f9fc;
        padding: 15px;
        font-weight: 600;
        color: #EA5626;
    }

    .table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Status and Badge Styles */
    .badge-module {
        background: #e0e7ff;
        color: #2E4374;
        padding: 0.4em 1em;
        border-radius: 50px;
        font-size: 0.75rem;
        margin: 0.2rem;
        display: inline-block;
    }

    .status-active {
        color: #10b981;
    }

    .status-inactive {
        color: #ef4444;
    }

    .avatar-circle {
        width: 35px;
        height: 35px;
        background: #e0e7ff;
        color: #2E4374;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .dash-nav-trigger {
            display: flex;
        }

        .dash-nav-links {
            display: none;
        }

        .dash-nav-links.show {
            display: block;
        }

        .dashboard-header {
            flex-direction: column;
            gap: 15px;
        }

        .card-header {
            flex-direction: column;
            gap: 15px;
        }

        .search-box {
            width: 100%;
        }
    }

    /* Update container and width styles */
    .dashboard-container {
        width: 100%;
        padding: 0;
        margin: 0;
    }

    .dashboard-content {
        width: 100%;
        max-width: 100%;
        padding: 20px;
        margin: 0;
    }

    /* Update client dashboard styles */
    .client-dashboard {
        padding: 0;
        width: 100%;
    }

    .dashboard-header {
        margin: 0 0 20px 0;
        padding: 20px;
        width: 100%;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .client-card {
        width: 100%;
        margin: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    /* Table responsive updates */
    .table-responsive {
        width: 100%;
        margin: 0;
        padding: 0;
        overflow-x: auto;
    }

    .table {
        min-width: 1000px;
        /* Minimum width to prevent squishing */
        width: 100%;
    }

    /* Adjust card padding */
    .card-body {
        padding: 0;
        /* Remove default padding */
    }

    .card-header {
        padding: 20px;
        margin: 0;
        width: 100%;
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .dashboard-content {
            padding: 15px;
        }

        .dashboard-header {
            padding: 15px;
        }

        .card-header {
            padding: 15px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-content {
            padding: 10px;
        }

        .dashboard-header {
            padding: 15px;
        }

        .search-box {
            width: 100%;
        }
    }

    /* dash-Navigation width adjustment */
    .dashboard-dash-nav {
        width: 100%;
        max-width: 100%;
        margin: 20px 0;
        padding: 0 20px;
    }

    .dash-nav-links {
        max-width: 100%;
        justify-content: flex-start;
        overflow-x: auto;
        padding: 15px 0;
    }

    /* Custom scrollbar for horizontal scroll */
    .dash-nav-links::-webkit-scrollbar {
        height: 6px;
    }

    .dash-nav-links::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
    }

    .dash-nav-links::-webkit-scrollbar-thumb {
        background: rgba(46, 67, 116, 0.3);
        border-radius: 3px;
    }

    .dash-nav-links::-webkit-scrollbar-thumb:hover {
        background: rgba(46, 67, 116, 0.5);
    }
</style>

<style>
    /* Mobile Toggle Button Styles */
    .mobile-toggle {
        display: none;
        position: fixed;
        top: 110px; /* Adjust based on your header height */
        right: 15px;
        z-index: 1001;
        background: #2E4374;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Navigation Styles */
    .dashboard-dash-nav {
        transition: all 0.3s ease;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .mobile-toggle {
            display: block;
        }

        .dashboard-dash-nav {
            position: fixed;
            top: 100px; /* Adjust based on your header height */
            right: -250px; /* Hide off-screen initially */
            width: 250px;
            height: calc(100vh - 100px);
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: right 0.3s ease;
        }

        .dashboard-dash-nav.active {
            right: 0;
        }

        .dash-nav-links {
            flex-direction: column;
            padding: 20px 0;
            height: 100%;
            overflow-y: auto;
        }

        .dash-nav-link {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            width: 100%;
        }

        /* Add overlay when menu is open */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .menu-overlay.active {
            display: block;
        }
    }
</style>

<!-- Add the mobile toggle button and overlay -->
<button class="mobile-toggle" id="mobileToggle">
    <i class="fas fa-bars"></i>
</button>
<div class="menu-overlay" id="menuOverlay"></div>

<!-- Update your existing navigation -->
<div class="dashboard-dash-nav">
    <ul class="dash-nav-links">
        <li><a href="#overview" class="dash-nav-link active" data-section="overview">
                <i class="fas fa-chart-line"></i> <span>Overview</span>
            </a></li>
        <li><a href="#clients" class="dash-nav-link" data-section="clients">
                <i class="fas fa-building"></i> <span>Clients</span>
            </a></li>
        <li><a href="#tickets" class="dash-nav-link" data-section="tickets">
                <i class="fas fa-ticket-alt"></i> <span>Tickets</span>
            </a></li>
        <li><a href="#contracts" class="dash-nav-link" data-section="contracts">
                <i class="fas fa-file-contract"></i> <span>Contracts</span>
            </a></li>
        <li><a href="#users" class="dash-nav-link" data-section="users">
                <i class="fas fa-users"></i> <span>Users</span>
            </a></li>
    </ul>
</div>

<div class="dashboard-container" style=" padding:80px;">
    <!-- Users Section -->
    <div id="users" class="content-section p-4" style="margin-top: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class=" fw-bold mb-0" style="color: #1B1D4E; padding:10px 0;">
                <i class="fas fa-users me-2"></i>User Management
            </h2>
            <div class="d-flex align-items-center">
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control border-end-0" id="userSearch" placeholder="Search users...">
                    <span class="input-group-text bg-white border-start-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="usersTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="color: #1B1D4E;" class="px-4 py-3">Name</th>
                                <th style="color: #1B1D4E;" class="py-3">Company</th>
                                <th style="color: #1B1D4E;" class="py-3">Company Email</th>
                                <th style="color: #1B1D4E;" class="py-3">Role</th>
                                <th style="color: #1B1D4E;" class="py-3">Status</th>
                                <th style="color: #1B1D4E;" class="py-3">Created</th>
                                <th style="color: #1B1D4E;" class="py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr id="user-<?= $user->id ?>">
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <!-- <div class="avatar-sm bg-primary-subtle rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <span class="text-primary fw-bold"><?= strtoupper(substr($user->name, 0, 1)) ?></span>
                                            </div> -->
                                            <div class="fw-medium"><?= Html::encode($user->name) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= Html::a(
                                            Html::encode($user->company_name),
                                            ['site/profile', 'id' => $user->company_id],
                                            ['class' => 'text-decoration-none fw-medium']
                                        ) ?>
                                    </td>
                                    <td>
                                        <div class="text-muted">
                                            <?= Html::encode($user->getAttribute('company_email')) ?: '<span class="fst-italic">Not set</span>' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info px-3 py-2">
                                            <?= Html::encode(ucfirst($user->role)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $user->status == 10
                                                                ? 'bg-success-subtle text-success'
                                                                : 'bg-danger-subtle text-danger' ?> px-3 py-2 status-badge">
                                            <?= getStatusText($user->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-muted">
                                            <?php if (isset($user->created_at) && $user->created_at): ?>
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?= Yii::$app->formatter->asDate($user->created_at, 'php:M d, Y') ?>
                                            <?php else: ?>
                                                <span class="fst-italic">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <?php if ($user->role != 4): ?>
                                                <?php if ($user->status == 10): ?>
                                                    <button class="btn btn-outline-danger btn-sm status-toggle"
                                                        onclick="toggleUserStatus(<?= $user->id ?>, 0)">
                                                        <i class="fas fa-ban me-1"></i>Deactivate
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-success btn-sm status-toggle"
                                                        onclick="toggleUserStatus(<?= $user->id ?>, 10)">
                                                        <i class="fas fa-check me-1"></i>Activate
                                                    </button>
                                                <?php endif; ?>

                                                <button class="btn btn-outline-danger btn-sm ms-2"
                                                    onclick="confirmDelete(<?= $user->id ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Protected Account</span>
                                            <?php endif; ?>
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

    <!-- Overview Section -->
    <div id="overview" class="content-section p-4 active">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color: #1B1D4E; padding:10px 0;">Dashboard Overview</h2>
        </div>
        <!-- Add this section to your admin dashboard -->

        <div class="card">
            <div class="card-header">
                <h3 class="">Clients with Comments</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th style="color: #1B1D4E;">ID</th>
                                <th style="color: #1B1D4E;">Status</th>
                                <th style="color: #1B1D4E;">Created At</th>
                                <th style="color: #1B1D4E;">Comments</th>
                                <th style="color: #1B1D4E;">Company Name</th>
                                <th style="color: #1B1D4E;">Closed By</th>
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
                                        <td><?= Html::encode($ticket->company_name) ?></td>
                                        <td><?= Html::encode($ticket->closed_by) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (!array_filter($tickets, function ($ticket) {
                        return !empty($ticket->comments);
                    })): ?>
                        <div class="alert alert-info">
                            No tickets with comments found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ticket Analytics Summary -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Top 5 Companies by Tickets</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get top 5 companies by ticket count
                        $topCompanies = Ticket::find()
                            ->select(['users.company_name', 'COUNT(*) as ticket_count'])
                            ->join('LEFT JOIN', 'users', 'ticket.created_by = users.id')
                            ->groupBy(['users.company_name'])
                            ->having(['IS NOT', 'users.company_name', null])
                            ->orderBy(['ticket_count' => SORT_DESC])
                            ->limit(5)
                            ->asArray()
                            ->all();

                        if (!empty($topCompanies)):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Company</th>
                                            <th>Tickets</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($topCompanies as $company): 
                                            if (!empty($company['company_name'])):
                                        ?>
                                            <tr>
                                                <td><?= $rank++ ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-2">
                                                            <?= !empty($company['company_name']) ? strtoupper(substr($company['company_name'], 0, 1)) : '?' ?>
                                                        </div>
                                                        <div>
                                                            <strong><?= Html::encode($company['company_name']) ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $company['ticket_count'] ?></span>
                                                </td>
                                            </tr>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No ticket data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Top 5 Active Developers</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (!empty($topDevelopers)):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Developer</th>
                                            <th>Assigned</th>
                                            <th>Resolution Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($topDevelopers as $developer): 
                                            if (!empty($developer['developer_name'])):
                                                $resolutionRate = $developer['assigned_count'] > 0 ? 
                                                    round(($developer['resolved_count'] / $developer['assigned_count']) * 100) : 0;
                                        ?>
                                            <tr>
                                                <td><?= $rank++ ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-2 bg-success-light">
                                                            <?= strtoupper(substr($developer['developer_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <strong><?= Html::encode($developer['developer_name']) ?></strong>
                                                            <?php if (!empty($developer['company_name'])): ?>
                                                                <br>
                                                                <small class="text-muted"><?= Html::encode($developer['company_name']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?= $developer['assigned_count'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-success" 
                                                             role="progressbar" 
                                                             style="width: <?= $resolutionRate ?>%"
                                                             aria-valuenow="<?= $resolutionRate ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= $resolutionRate ?>%</small>
                                                </td>
                                            </tr>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No developer data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients Section -->


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('contractSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const visible = Array.from(row.getElementsByTagName('td'))
                    .some(td => td.textContent.toLowerCase().includes(searchValue));
                row.style.display = visible ? '' : 'none';
            });
        });
    </script>


    <!-- contracts section -->
    <div id="contracts" class="content-section p-4 active">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold mb-0" style="color: #1B1D4E; padding:20px 0;">Contract Renewals</h2>
        </div>

        <div>
            <?= Html::a(
                '<i class="fas fa-eye" style="margin-right: 5px;"></i> View All Renewals',
                ['contract/index'],
                ['class' => 'btn btn-primary mb-2']
            ) ?>
        </div>

        <?php
        // Group contract renewals by company
        $groupedRenewals = [];
        $companyNames = [];
        
        foreach ($contractRenewals as $renewal) {
            $companyId = null;
            $companyName = 'Unknown';
            
            // Get company information
            if (!empty($renewal->requested_by)) {
                $user = User::findOne($renewal->requested_by);
                if ($user && $user->company_id) {
                    $company = Company::findOne($user->company_id);
                    if ($company) {
                        $companyId = $company->id;
                        $companyName = $company->name;
                    }
                }
            }
            
            // Use company ID as group key
            $key = $companyId ?: 'unknown-' . $renewal->id;
            
            if (!isset($groupedRenewals[$key])) {
                $groupedRenewals[$key] = [];
                $companyNames[$key] = $companyName;
            }
            
            $groupedRenewals[$key][] = $renewal;
        }
        
        // Create a new array with one renewal per company (the most recent one)
        $displayRenewals = [];
        foreach ($groupedRenewals as $companyId => $renewals) {
            // Sort renewals by created_at (newest first)
            usort($renewals, function($a, $b) {
                return strtotime($b->created_at) - strtotime($a->created_at);
            });
            
            // Add the most recent renewal to display
            $displayRenewals[] = [
                'renewal' => $renewals[0],
                'company_name' => $companyNames[$companyId],
                'count' => count($renewals),
                'company_id' => $companyId
            ];
        }
        
        if (!empty($displayRenewals)):
        ?>
            <div class="table-responsive">
                <table class="table table-hover" id="renewalsTable">
                    <thead>
                        <tr>
                            <th style="color: #1B1D4E;">Company Name</th>
                            <th style="color: #1B1D4E;">Extension Period</th>
                            <th style="color: #1B1D4E;">Current End Date</th>
                            <th style="color: #1B1D4E;">New End Date</th>
                            <th style="color: #1B1D4E;">Notes</th>
                            <th style="color: #1B1D4E;">Status</th>
                            <th style="color: #1B1D4E;">Created At</th>
                            <th style="color: #1B1D4E;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($displayRenewals as $item): ?>
                            <?php $renewal = $item['renewal']; ?>
                            <tr>
                                <td data-label="Company Name">
                                    <?= Html::encode($item['company_name']) ?>
                                    <?php if ($item['count'] > 1): ?>
                                        <span class="badge bg-info ms-2" title="Multiple renewals">
                                            <?= $item['count'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Extension Period">
                                    <?= $renewal->renewal_duration ?> Months
                                </td>
                                <td data-label="Current End Date">
                                    <?= Yii::$app->formatter->asDate($renewal->current_end_date) ?>
                                </td>
                                <td data-label="New End Date">
                                    <?= Yii::$app->formatter->asDate($renewal->new_end_date) ?>
                                </td>
                                <td data-label="Notes">
                                    <?= Html::encode($renewal->notes) ?>
                                </td>
                                <td data-label="Status">
                                    <?= $renewal->getStatusLabel() ?>
                                </td>
                                <td data-label="Created At">
                                    <?= Yii::$app->formatter->asDatetime($renewal->created_at) ?>
                                </td>
                                <td data-label="Actions">
                                    <?php if ($renewal->renewal_status !== 'approved'): ?>
                                        <div class="btn-group">
                                            <button type="button" 
                                                class="btn btn-success btn-sm update-status-btn" 
                                                    data-id="<?= $renewal->id ?>" 
                                                    data-status="approved">
                                            Approve
                                            </button>
                                            <button type="button" 
                                                class="btn btn-danger btn-sm update-status-btn" 
                                                    data-id="<?= $renewal->id ?>" 
                                                    data-status="rejected">
                                            Reject
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-sm" disabled>
                                            Approved
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['count'] > 1): ?>
                                        <?= Html::a(
                                            '<i class="fas fa-list"></i> View All',
                                            ['contract/company', 'id' => $item['company_id']],
                                            ['class' => 'btn btn-info btn-sm ms-2']
                                        ) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No contract renewals found.</div>
        <?php endif; ?>
    </div>

    <?php
    $updateStatusUrl = Url::to(['site/update-renewal-status']);
    $csrfToken = Yii::$app->request->csrfToken;

    $js = <<<JS
    // First, verify jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
    } else {
        console.log('jQuery is loaded');
    }

    // Simple click handler
    $('.update-status-btn').click(function(e) {
            e.preventDefault();
        console.log('Button clicked');
            
            var btn = $(this);
            var id = btn.data('id');
            var status = btn.data('status');
            
        console.log({
            'Button clicked': true,
            'Button ID': id,
            'Status': status,
            'URL': '$updateStatusUrl',
            'CSRF': '$csrfToken'
        });
            
            if (confirm('Are you sure you want to ' + status + ' this renewal request?')) {
                $.ajax({
                    url: '$updateStatusUrl',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        status: status,
                        _csrf: '$csrfToken'
                    },
                    beforeSend: function() {
                    console.log('Sending request...');
                        btn.prop('disabled', true);
                    },
                    success: function(response) {
                    console.log('Success response:', response);
                        if (response.success) {
                            alert(response.message);
                        location.reload();
                        } else {
                            alert(response.message || 'Failed to update status');
                        }
                    },
                    error: function(xhr, status, error) {
                    console.error('Error details:', {
                        error: error,
                        status: status,
                        response: xhr.responseText
                    });
                    alert('Error occurred: ' + error);
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            }
    });
JS;

    // Register the JavaScript in POS_END to ensure DOM is loaded
    $this->registerJs($js, \yii\web\View::POS_END);
    ?>

    <div id="clients" class="body-client content-section">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold mb-0" style="color:#1B1D4E; padding:10px 0;">Clients</h2>
        </div>
        <!-- Clients content -->

        <style>
            .client-dashboard {
                padding: 0px;
            }

            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding: 1rem;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .client-card {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                border: none;
            }

            .card-header {

                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px 8px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .table {
                margin: 0;
            }

            .table thead th {
                background-color: #f8f9fc;
                color: #4e73df;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.85rem;
                padding: 1rem;
                border-bottom: 2px solid #e3e6f0;
            }

            .table tbody tr {
                transition: all 0.2s ease;
            }

            .table tbody tr:hover {
                background-color: #f8f9fc;
            }

            .table td {
                padding: 1rem;
                vertical-align: middle;
            }

            .badge-module {
                background: #e3e6f0;
                color: #4e73df;
                padding: 0.4em 0.8em;
                border-radius: 50px;
                font-size: 0.75rem;
                margin: 0.2rem;
                display: inline-block;
            }

            .status-active {
                color: #1cc88a;
            }

            .status-inactive {
                color: #e74a3b;
            }

            .search-box {
                max-width: 300px;
                margin-bottom: 1rem;
            }

            .action-buttons a {
                margin-left: 0.5rem;
            }
        </style>

        <section id="clients" class="client-section">
            <div class="client-dashboard">
                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-building"></i> Client Management</h2>
                        <p class="mb-0 text-gray-600">Manage your company clients</p>
                    </div>
                    <div>
                        <?= Html::a('<i class="fas fa-plus"></i> Add New Client', ['/site/add-client'], [
                            'class' => 'btn btn-primary'
                        ]) ?>
                    </div>
                </div>

                <!-- Client List Card -->
                <div class="card client-card">
                    <div class="card-header">
                        <h3 class="mb-0" style="color:#1B1D4E; padding:10px 0;">Client List</h3>
                        <div class="search-box">
                            <input type="text" id="clientSearch" class="form-control" placeholder="Search clients...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <?php
                            $clients = \app\models\Client::find()->all();
                            if (!empty($clients)):
                            ?>
                                <table class="table" id="clientTable">
                                    <thead>
                                        <tr>
                                            <th style="color: #1B1D4E;" >ID</th>
                                            <th style="color: #1B1D4E;" >Company Name</th>
                                            <th style="color: #1B1D4E;" >Email</th>
                                            <th style="color: #1B1D4E;" >Modules</th>
                                            <th style="color: #1B1D4E;" >Status</th>
                                            <th style="color: #1B1D4E;" >Created At</th>
                                            <th style="color: #1B1D4E;" >Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clients as $client): ?>
                                            <tr>
                                                <td><strong>#<?= Html::encode($client->id) ?></strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-2">
                                                            <?= strtoupper(substr($client->company_name, 0, 1)) ?>
                                                        </div>
                                                        <?= Html::encode($client->company_name) ?>
                                                    </div>
                                                </td>
                                                <td class="text-black">
                                                    <a class="text-black" href="mailto:<?= Html::encode($client->company_email) ?>">
                                                        <?= Html::encode($client->company_email) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (!empty($client->module)):
                                                        $modules = explode(',', $client->module);
                                                        foreach ($modules as $module):
                                                            if (trim($module)):
                                                    ?>
                                                                <span class="badge-module"><?= Html::encode(trim($module)) ?></span>
                                                    <?php
                                                            endif;
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Check if status column exists, if not default to 'active'
                                                    $statusDisplay = isset($client->is_active) ? $client->is_active : (isset($client->status_id) ? $client->status_id : 1);
                                                    $isActive = $statusDisplay == 1;
                                                    ?>
                                                    <span class="<?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                                        <i class="fas fa-circle fa-sm"></i>
                                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div><?= Yii::$app->formatter->asDate($client->created_at) ?></div>
                                                    <small class="text-muted">
                                                        <?= Yii::$app->formatter->asTime($client->created_at) ?>
                                                    </small>
                                                </td>
                                                <td class="action-buttons">
                                                    <?= Html::a('<i class="fas fa-edit"></i>', ['/client/update', 'id' => $client->id], [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'title' => 'Edit Client'
                                                    ]) ?>
                                                    <?= Html::a('<i class="fas fa-eye"></i>', ['/client/view', 'id' => $client->id], [
                                                        'class' => 'btn btn-sm btn-outline-info',
                                                        'title' => 'View Details'
                                                    ]) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No clients found. Start by adding a new client.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php
        // Register required assets
        $this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

        // Add search functionality
        $this->registerJs("
    $('#clientSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#clientTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
");
        ?>

        <!-- Reports Section -->
        <div id="reports" class="content-section">
            <h2>Reports</h2>
            <!-- Reports content -->
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // User filter functionality
    $('.btn-group [data-filter]').click(function() {
        $(this).addClass('active').siblings().removeClass('active');
        const filter = $(this).data('filter');
        
        if (filter === 'all') {
            $('#usersTable tbody tr').show();
        } else {
            $('#usersTable tbody tr').hide();
            $('#usersTable tbody tr[data-status=\"' + filter + '\"]').show();
        }
    });

    // User search functionality
    $('#userSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#usersTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // dash-Navigation functionality
    $('.dash-dash-nav-item').click(function(e) {
        e.preventDefault();
        $('.dash-dash-nav-item').removeClass('active');
        $(this).addClass('active');
        
        const section = $(this).data('section');
        $('.content-section').removeClass('active');
        $('#' + section).addClass('active');
    });
");
?>

<?php
// Register required assets
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

// Prepare data for the chart
$chartData = [
    'labels' => ['Open', 'In Progress', 'Resolved', 'Closed'],
    'datasets' => [[
        'data' => [$openTickets, $inProgressTickets, $resolvedTickets, $closedTickets],
        'backgroundColor' => ['#dc3545', '#ffc107', '#28a745', '#17a2b8'],
    ]],
];

// Register the chart initialization script
$this->registerJs("
    new Chart(document.getElementById('ticketStatusChart'), {
        type: 'doughnut',
        data: " . json_encode($chartData) . ",
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
", \yii\web\View::POS_READY);
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

<!-- Add this modal at the end of your file -->
<div class="modal fade" id="contractExtensionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Extend Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="extensionUserId">
                <input type="hidden" id="extensionCompanyName">

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Current contract expires: <span id="currentExpiryDate"></span>
                </div>

                <div class="mb-3">
                    <label for="extensionPeriod" class="form-label">Extension Period</label>
                    <select class="form-control" id="extensionPeriod">
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12" selected>1 Year</option>
                        <option value="24">2 Years</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="extendContract()">
                    <i class="fas fa-check"></i> Confirm Extension
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update the JavaScript code -->
<?php
$csrfToken = Yii::$app->request->getCsrfToken();
$js = <<<JS
function showContractExtension(userId, companyName) {
    document.getElementById('extensionUserId').value = userId;
    document.getElementById('extensionCompanyName').value = companyName;
    
    // Fetch and display current expiry date
    $.get('/site/get-company-expiry', { companyName: companyName }, function(response) {
        if (response.success) {
            document.getElementById('currentExpiryDate').textContent = response.expiryDate;
        }
    });
    
    const modal = new bootstrap.Modal(document.getElementById('contractExtensionModal'));
    modal.show();
}

function extendContract() {
    const userId = document.getElementById('extensionUserId').value;
    const companyName = document.getElementById('extensionCompanyName').value;
    const extensionPeriod = document.getElementById('extensionPeriod').value;
    
    Swal.fire({
        title: 'Confirm Contract Extension',
        text: 'Are you sure you want to extend this contract?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, extend it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/site/extend-contract',
                type: 'POST',
                data: {
                    userId: userId,
                    companyName: companyName,
                    extensionPeriod: extensionPeriod,
                    _csrf: '$csrfToken'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to extend contract'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing your request'
                    });
                }
            });
        }
    });
}
JS;
$this->registerJs($js);
?>

<?php
$this->registerJs("
    // Mobile menu functionality
    const mobileToggle = document.getElementById('mobileToggle');
    const dashNav = document.querySelector('.dashboard-dash-nav');
    const menuOverlay = document.getElementById('menuOverlay');
    const navLinks = document.querySelectorAll('.dash-nav-link');

    function toggleMenu() {
        dashNav.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        
        // Toggle icon
        const icon = mobileToggle.querySelector('i');
        if (dashNav.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }

    // Toggle menu when button is clicked
    mobileToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    // Close menu when overlay is clicked
    menuOverlay.addEventListener('click', toggleMenu);

    // Close menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMenu();
            }
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            dashNav.classList.contains('active') && 
            !dashNav.contains(e.target) && 
            e.target !== mobileToggle) {
            toggleMenu();
        }
    });

    // Prevent menu from staying open on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            dashNav.classList.remove('active');
            menuOverlay.classList.remove('active');
            const icon = mobileToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });
");
?>

<script>
    function confirmDelete(userId) {
        if (confirm("Are you sure you want to delete this user? This action cannot be undone.")) {
            // Make an AJAX request to delete the user
            $.ajax({
                url: '<?= \yii\helpers\Url::to(['user/delete']) ?>', // Adjust the URL to your delete action
                type: 'POST',
                data: {
                    id: userId
                },
                success: function(response) {
                    if (response.success) {
                        alert("User deleted successfully.");
                        location.reload(); // Reload the page to see the changes
                    } else {
                        alert("Error deleting user: " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while trying to delete the user.");
                }
            });
        }
    }
</script>

<?php
$this->registerJs("
    function toggleUserStatus(userId, newStatus) {
        $.ajax({
            url: '" . Yii::$app->urlManager->createUrl(['site/toggle-status']) . "',
            type: 'POST',
            data: {
                id: userId,
                status: newStatus,
                '" . Yii::$app->request->csrfParam . "': '" . Yii::$app->request->csrfToken . "'
            },
            success: function(response) {
                if (response.success) {
                    const statusBadge = $('#user-' + userId + ' .status-badge');
                    const statusToggle = $('#user-' + userId + ' .status-toggle');
                    
                    if (newStatus == 10) {
                        statusBadge.removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success');
                        statusBadge.text('Active');
                        statusToggle.removeClass('btn-outline-success').addClass('btn-outline-danger');
                        statusToggle.html('<i class=\"fas fa-ban me-1\"></i>Deactivate');
                        statusToggle.attr('onclick', 'toggleUserStatus(' + userId + ', 0)');
                    } else {
                        statusBadge.removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger');
                        statusBadge.text('Inactive');
                        statusToggle.removeClass('btn-outline-danger').addClass('btn-outline-success');
                        statusToggle.html('<i class=\"fas fa-check me-1\"></i>Activate');
                        statusToggle.attr('onclick', 'toggleUserStatus(' + userId + ', 10)');
                    }
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                } else {
                    console.error(response.message);
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message);
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to update status');
                } else {
                    alert('Failed to update status');
                }
            }
        });
    }
");
?>

<?php
$this->registerJs("
    function updateRenewalStatus(id, status) {
        if (!id || !status) {
            console.error('Missing parameters');
            return;
        }

        if (!confirm('Are you sure you want to ' + status + ' this renewal request?')) {
            return;
        }

        const button = $('button[onclick*=\"' + id + '\"]');
        const originalText = button.html();
        button.prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin\"></i> Processing...');

        $.ajax({
            url: '" . Yii::$app->urlManager->createUrl(['site/update-renewal-status']) . "',
            type: 'POST',
            data: {
                id: id,
                status: status,
                _csrf: yii.getCsrfToken()
            },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                alert(response.message);
                window.location.reload();
            } else {
                alert('Error: ' + (response.message || 'Unknown error occurred'));
                button.prop('disabled', false).html(originalText);
            }
        })
        .fail(function(jqXHR) {
            let errorMessage = 'Failed to update status';
            try {
                const response = JSON.parse(jqXHR.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {}
            
            alert(errorMessage);
            button.prop('disabled', false).html(originalText);
            console.error('Update failed:', jqXHR.responseText);
        });
    }
", View::POS_END);
?>

<style>
    /* Update existing container styles */
    .container {
        max-width: 100%;
        padding: 0;
        overflow-x: hidden; /* Prevent horizontal scroll */
    }

    /* Update dashboard container padding for mobile */
    .dashboard-container {
        padding: 20px !important; /* Override inline style */
    }

    /* Make tables responsive */
    .table-responsive {
        width: 100%;
        margin: 0;
        padding: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    }

    /* Adjust card padding for mobile */
    .card-body {
        padding: 15px;
    }

    /* Make buttons and inputs full width on mobile */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 10px !important;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 5px;
        }

        .btn-group .btn {
            width: 100%;
            margin: 0;
        }

        .input-group {
            width: 100% !important;
        }

        /* Adjust table display for mobile */
        .table td, .table th {
            min-width: 120px; /* Ensure minimum width for content */
        }

        /* Stack action buttons vertically */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .action-buttons .btn {
            width: 100%;
            margin: 0;
        }

        /* Adjust navigation for mobile */
        .dash-nav-links {
            flex-direction: column;
            width: 100%;
            padding: 0;
        }

        .dash-nav-link {
            width: 100%;
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        /* Make search inputs full width */
        #userSearch, #clientSearch {
            width: 100% !important;
        }

        /* Adjust header elements */
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }

        .d-flex.justify-content-between > * {
            width: 100%;
        }

        /* Adjust modal for mobile */
        .modal-dialog {
            margin: 10px;
        }
    }

    /* Additional mobile optimizations */
    @media (max-width: 576px) {
        h2, h3 {
            font-size: 1.5rem;
        }

        .badge {
            display: inline-block;
            margin: 2px;
        }

        .table td {
            padding: 10px;
        }
    }
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</style>