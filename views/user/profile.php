<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\ActiveForm;
use app\models\Ticket;
use app\models\User;
use app\models\Company;
use app\models\ContractRenewal;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $company app\models\Company */
/* @var $companyDetails array */
/* @var $tickets app\models\Ticket[] */
/* @var $renewals app\models\ContractRenewal[] */

// $this->title = "<span style='color: #007bff; font-weight: bold;'>" . Html::encode($companyDetails['company_name']) . "</span> Profile";
// Initialize variables
$duration = 'Not available';
$isExpired = false;
$today = new DateTime();
$remainingDays = 0;
$totalDays = 0;
$progressPercentage = 0;

// Calculate duration and status if dates exist
if ($companyDetails['start_date'] && $companyDetails['end_date']) {
    try {
        $startDate = new DateTime($companyDetails['start_date']);
        $endDate = new DateTime($companyDetails['end_date']);

        // Calculate total duration
        $interval = $startDate->diff($endDate);
        $duration = '';
        if ($interval->y > 0) $duration .= $interval->y . ' years ';
        if ($interval->m > 0) $duration .= $interval->m . ' months ';
        if ($interval->d > 0) $duration .= $interval->d . ' days';

        // Calculate remaining time
        $remainingTime = $today->diff($endDate);
        $isExpired = $today > $endDate;
        $remainingDays = $remainingTime->days;
        $totalDays = $interval->days;

        // Calculate progress
        if ($totalDays > 0) {
            $usedDays = $totalDays - $remainingDays;
            $progressPercentage = ($usedDays / $totalDays) * 100;
        }

        // Show renew button if 10 or fewer days remaining or expired
        $showRenewButton = $remainingDays <= 10 || $isExpired;
    } catch (\Exception $e) {
        Yii::error("Date calculation error: " . $e->getMessage());
        $duration = 'Error calculating duration';
    }
}

// Single role check at the top
$currentUserRole = Yii::$app->user->identity->role;
$isSuperAdmin = $currentUserRole === User::ROLE_SUPER_ADMIN; // Using constant from User model

// At the top of the view, add debug information
Yii::debug("View - Current user role: " . Yii::$app->user->identity->role);
Yii::debug("View - Is Super Admin: " . ($isSuperAdmin ? 'true' : 'false'));
?>

<div class="container" style="padding:30px;">
    <?php if ($isSuperAdmin): ?>
        <!-- Super Admin View -->
        <div class="super-admin-profile">
            <h1>Super Admin Dashboard</h1>
            
            <!-- Users List -->
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">System Users</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Company</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $userData): ?>
                                    <tr>
                                        <td><?= Html::encode($userData['username']) ?></td>
                                        <td><?= Html::encode($userData['company_name']) ?></td>
                                        <td>
                                            <?php
                                            $roleLabels = [
                                                User::ROLE_USER => '<span class="badge bg-secondary">User</span>',
                                                User::ROLE_ADMIN => '<span class="badge bg-info">Admin</span>',
                                                User::ROLE_DEVELOPER => '<span class="badge bg-warning">Developer</span>',
                                                User::ROLE_SUPER_ADMIN => '<span class="badge bg-danger">Super Admin</span>'
                                            ];
                                            echo $roleLabels[$userData['role']] ?? 'Unknown';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $userData['status'] ? 'success' : 'danger' ?>">
                                                <?= $userData['status'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= Html::button('Toggle Status', [
                                                'class' => 'btn btn-sm btn-warning toggle-status',
                                                'data-id' => $userData['id']
                                            ]) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title">Total Users</h5>
                        </div>
                        <div class="card-body text-center">
                            <h2><?= count($allUsers) ?></h2>
                        </div>
                    </div>
                </div>
                <!-- Add more statistics cards as needed -->
            </div>
        </div>

    <?php else: ?>
        <!-- Regular User View -->
        <div class="company-profile container" style="padding:30px;">
            <?php
            $this->title = "Profile : <span style='color: #EA5626; font-weight: normal;'>" . Html::encode($companyDetails['company_name']) . "</span> ";
            ?>
            <h1 style="margin-bottom: 15px;"><?= $this->title ?></h1>

            <!-- Basic Profile Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <!-- Existing profile information -->
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Profile:</strong> <?= Html::encode($user->username) ?></p>
                            <p><strong>Company Name:</strong> <?= Html::encode($user->company_name) ?></p>
                            <p><strong>Company Email:</strong> <?= Html::encode($user->company_email ?: '(not set)') ?></p>
                            <p><strong>Role:</strong> <?= Html::encode($user->role) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <?= Html::encode($user->status ? 'Active' : 'Inactive') ?></p>
                            <p><strong>Created At:</strong> <?= Html::encode($user->created_at) ?></p>
                            <p><strong>Updated At:</strong> <?= Html::encode($user->updated_at) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$isSuperAdmin): ?>
            <!-- Show contract details only for regular companies -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0">Contract Period</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Start Date:</th>
                                    <td><?= Yii::$app->formatter->asDate($companyDetails['start_date']) ?></td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td><?= Yii::$app->formatter->asDate($companyDetails['end_date']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Duration:</th>
                                    <td><?= $duration ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header <?= $isExpired ? 'bg-danger' : 'bg-success' ?> text-white">
                            <h3 class="card-title mb-0">Contract Status</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($companyDetails['start_date'] && $companyDetails['end_date']): ?>
                                <?php if ($isExpired): ?>
                                    <div class="alert alert-danger">
                                        <strong>Contract Expired!</strong><br>
                                        Contract ended <?= $remainingDays ?> days ago
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <strong>Contract Active</strong><br>
                                        <?= $remainingDays ?> days remaining
                                    </div>
                                <?php endif; ?>

                                <?php if ($showRenewButton): ?>
                                    <div class="mt-3">
                                        <?php
                                        // Debug information
                                        Yii::debug("Company Details: " . print_r($companyDetails, true));
                                        ?>

                                        <!-- Debug output visible on page -->
                                        <div class="alert alert-info">
                                            <small>
                                                Company ID: <?= $companyDetails['id'] ?? 'Not set' ?><br>
                                                Current URL: <?= Yii::$app->request->url ?><br>
                                            </small>
                                        </div>

                                        <?= Html::a(
                                            '<i class="fas fa-sync-alt"></i> Renew Contract',
                                            ['/site/renew-contract', 'id' => $companyDetails['id']], // Updated route with leading slash
                                            [
                                                'class' => 'btn btn-warning btn-block',
                                                'data' => [
                                                    'method' => 'get',
                                                    'params' => ['id' => $companyDetails['id']]
                                                ]
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    Contract dates not set
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Super Admin Dashboard Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0">Administrative Overview</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            // Debug output
                            Yii::debug("CompanyStats: " . print_r($companyStats, true));
                            ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5>Total Companies</h5>
                                            <h2><?= $companyStats['total_companies'] ?? 0 ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5>Active Contracts</h5>
                                            <h2><?= $companyStats['active_contracts'] ?? 0 ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5>Total Users</h5>
                                            <h2><?= $companyStats['total_users'] ?? 0 ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5>System Health</h5>
                                            <h2 class="text-success">Active</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isSuperAdmin): ?>
                <!-- Ticket Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ticket Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="stat-card bg-primary text-white">
                                    <h6>Total</h6>
                                    <h3><?= $companyDetails['ticketStats']['total'] ?></h3>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-card bg-info text-white">
                                    <h6>Open</h6>
                                    <h3><?= $companyDetails['ticketStats']['open'] ?></h3>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-card bg-warning text-white">
                                    <h6>In Progress</h6>
                                    <h3><?= $companyDetails['ticketStats']['in_progress'] ?></h3>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-card bg-success text-white">
                                    <h6>Resolved</h6>
                                    <h3><?= $companyDetails['ticketStats']['resolved'] ?></h3>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-card bg-secondary text-white">
                                    <h6>Closed</h6>
                                    <h3><?= $companyDetails['ticketStats']['closed'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Ticket Raisers -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Top Ticket Raisers</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($topRaisers)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Company</th>
                                                    <th>Tickets</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($topRaisers as $raiser): ?>
                                                    <tr>
                                                        <td><?= Html::encode($raiser['created_by']) ?></td>
                                                        <td><?= Html::encode($raiser['company_name'] ?? 'N/A') ?></td>
                                                        <td><span class="badge bg-primary"><?= $raiser['ticket_count'] ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">No data available</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Top Developers -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Most Active Developers</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($topDevelopers)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Developer</th>
                                                    <th>Company</th>
                                                    <th>Assigned</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($topDevelopers as $developer): ?>
                                                    <tr>
                                                        <td><?= Html::encode($developer['assigned_to']) ?></td>
                                                        <td><?= Html::encode($developer['company_name'] ?? 'N/A') ?></td>
                                                        <td><span class="badge bg-success"><?= $developer['assigned_count'] ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">No data available</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Users Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($allUsers)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Company</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allUsers as $userData): ?>
                                            <tr>
                                                <td><?= Html::encode($userData['username']) ?></td>
                                                <td><?= Html::encode($userData['company_name']) ?></td>
                                                <td><?= Html::encode($userData['company_email']) ?></td>
                                                <td><?= Html::encode($userData['role']) ?></td>
                                                <td>
                                                    <span class="badge <?= $userData['status'] ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $userData['status'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td><?= Html::encode(Yii::$app->formatter->asDatetime($userData['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No users found</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <style>
            body {
                font-family: 'Roboto', sans-serif;
                background-color: #f8f9fa;
                margin: 0;
                padding: 40px;
                color: #343a40;
                margin-top: 40px;
            }

            /* Container Styling */
            .container {
                max-width: 95%;
                padding: 20px;
                margin: 0 auto;
            }
            
            /* Add responsive table styles */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Card hover effect */
            .card {
                transition: transform 0.2s;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }

            /* Stats cards */
            .card-body h5 {
                color: #2c3e50;
                font-weight: 600;
                margin-bottom: 1rem;
            }

            .table-sm td, .table-sm th {
                padding: 0.3rem;
                font-size: 0.9rem;
            }
        </style>
        <style>
            /* Container Styling */


            .finefooter {
                padding: 0px 60px;
                margin-bottom: -20px;

            }
        </style>

        <!-- Update visibility logic -->
        <?php if ($isSuperAdmin): ?>
            <div class="alert alert-info">
                Debug Info:
                <pre>
                    Role: <?= Yii::$app->user->identity->role ?>
                    Is Super Admin: <?= $isSuperAdmin ? 'Yes' : 'No' ?>
                    Company Stats Available: <?= isset($companyStats) ? 'Yes' : 'No' ?>
                </pre>
            </div>
            
            <!-- Super Admin content here -->
        <?php else: ?>
            <!-- Regular user content here -->
        <?php endif; ?>

        <!-- Add temporary debug info -->
        <div class="alert alert-info">
            Debug Info:
            <pre>
            Current Role: <?= Yii::$app->user->identity->role ?>
            Is Super Admin: <?= $isSuperAdmin ? 'Yes' : 'No' ?>
            Company Stats Available: <?= isset($companyStats) ? 'Yes' : 'No' ?>
            </pre>
        </div>

        <?php if ($isSuperAdmin): ?>
            <div class="row mt-4">
                <!-- Users who raised tickets -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">Users with Most Tickets</h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach ($userTickets as $index => $user): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                            <?= Html::encode($user['username']) ?>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?= $user['ticket_count'] ?> tickets
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developers with assigned tickets -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title">Top Developers</h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach ($developers as $index => $dev): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                            <?= Html::encode($dev['username']) ?>
                                        </div>
                                        <span class="badge bg-success rounded-pill">
                                            <?= $dev['assigned_count'] ?> assigned
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .list-group-item {
                    margin-bottom: 5px;
                    border-radius: 5px;
                }
                .badge {
                    font-size: 0.9em;
                }
                .card {
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    margin-bottom: 20px;
                }
                .card-header {
                    font-weight: bold;
                }
            </style>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($isSuperAdmin): ?>
    <?php
    $this->registerJs("
        $('.toggle-status').click(function() {
            var userId = $(this).data('id');
            $.post('/user/toggle-status', {id: userId}, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            });
        });
    ");
    ?>
<?php endif; ?>

<style>
    .stat-card {
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h6 {
        margin-bottom: 8px;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .stat-card h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: bold;
    }

    .table {
        font-size: 0.9rem;
    }

    .badge {
        font-size: 0.8rem;
        padding: 5px 10px;
    }
</style>