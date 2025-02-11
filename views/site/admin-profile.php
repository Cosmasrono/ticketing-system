<?php
use yii\helpers\Html;
use app\models\User;
?>

<div class="admin-profile">
    <h1>Administrator Profile</h1>
    
    <div class="profile-info">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Profile Information</h5>
                <table class="table">
                    <tr>
                        <th>Name:</th>
                        <td><?= Html::encode($user->name) ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= Html::encode($user->company_email) ?></td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><?= Html::encode(ucfirst($user->role)) ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-success">Active</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Contracts Due Soon Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Contracts Due Soon</h5>
                <?php if (!empty($nearingContracts)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Company Name</th>
                                    <th>Email</th>
                                    <th>End Date</th>
                                    <th>Days Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nearingContracts as $contract): ?>
                                    <?php 
                                        $daysRemaining = floor((strtotime($contract->end_date) - time()) / (60 * 60 * 24));
                                        $statusClass = $daysRemaining <= 7 ? 'bg-danger' : 
                                                     ($daysRemaining <= 14 ? 'bg-warning' : 'bg-info');

                                        // Find the company admin user
                                        $companyAdmin = User::find()
                                            ->where(['company_name' => $contract->company_name])
                                            ->andWhere(['role' => '2']) // Assuming '2' is company admin role
                                            ->one();
                                        $companyEmail = $companyAdmin ? $companyAdmin->company_email : 'No email found';
                                    ?>
                                    <tr>
                                        <td><?= Html::encode($contract->company_name) ?></td>
                                        <td>
                                            <a href="mailto:<?= Html::encode($companyEmail) ?>">
                                                <?= Html::encode($companyEmail) ?>
                                            </a>
                                        </td>
                                        <td><?= Yii::$app->formatter->asDate($contract->end_date) ?></td>
                                        <td>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= $daysRemaining ?> days
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                                $status = $daysRemaining <= 7 ? 'Critical' : 
                                                        ($daysRemaining <= 14 ? 'Warning' : 'Upcoming');
                                            ?>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No contracts due soon.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Most Active Users Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Most Active Users</h5>
                <?php if (!empty($activeUsers)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Tickets Raised</th>
                                    <th>Most Used Module</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeUsers as $activeUser): ?>
                                    <tr>
                                        <td><?= Html::encode($activeUser['user_id']) ?></td>
                                        <td><?= Html::encode($activeUser['company_name']) ?></td>
                                        <td>
                                            <a href="mailto:<?= Html::encode($activeUser['company_email']) ?>">
                                                <?= Html::encode($activeUser['company_email']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?= $activeUser['ticket_count'] ?> tickets
                                            </span>
                                        </td>
                                        <td><?= Html::encode($activeUser['most_used_module']) ?></td>
                                        <td><?= Yii::$app->formatter->asDatetime($activeUser['last_ticket_date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No ticket data available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Most Assigned Staff Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Most Assigned Staff</h5>
                <?php if (!empty($busyDevelopers)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Staff ID</th>
                                    <th>Name</th>
                                    <th>Total Assigned</th>
                                    <th>Active Tickets</th>
                                    <th>Breached SLA</th>
                                    <th>Last Assignment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($busyDevelopers as $developer): ?>
                                    <tr>
                                        <td><?= Html::encode($developer['assigned_to']) ?></td>
                                        <td><?= Html::encode($developer['developer_name']) ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $developer['assigned_count'] ?> total
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <?= $developer['active_tickets'] ?> active
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <?= $developer['breached_tickets'] ?> breached
                                            </span>
                                        </td>
                                        <td><?= Yii::$app->formatter->asDatetime($developer['last_assigned']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No assignment data available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ticket Statistics Overview -->
        <?php if (isset($ticketStats)): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Ticket Statistics Overview</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="alert alert-warning">
                                <h6>Pending Tickets</h6>
                                <h3><?= $ticketStats['total_pending'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger">
                                <h6>Breached SLA</h6>
                                <h3><?= $ticketStats['total_breached'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-info">
                                <h6>At Risk</h6>
                                <h3><?= $ticketStats['total_at_risk'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-secondary">
                                <h6>High Severity</h6>
                                <h3><?= $ticketStats['severity_high'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Module Statistics Section -->
        <?php if (isset($moduleStats)): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Module Statistics</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Total Tickets</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($moduleStats as $module): ?>
                                    <tr>
                                        <td><?= Html::encode($module['module']) ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $module['count'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Breached Tickets Section -->
        <?php if (isset($breachedTickets)): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Breached SLA Tickets</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Module</th>
                                    <th>Issue</th>
                                    <th>Created At</th>
                                    <th>Response Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($breachedTickets)): ?>
                                    <?php foreach ($breachedTickets as $ticket): ?>
                                        <tr>
                                            <td><?= Html::encode($ticket->id) ?></td>
                                            <td><?= Html::encode($ticket->module) ?></td>
                                            <td><?= Html::encode($ticket->issue) ?></td>
                                            <td><?= Yii::$app->formatter->asDatetime($ticket->created_at) ?></td>
                                            <td><?= Html::encode($ticket->response_time) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No breached tickets found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-profile {
    padding: 20px;
}

.profile-info {
    max-width: 1000px; /* Increased to accommodate the wider table */
    margin: 0 auto;
}

.card {
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-title {
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.table th {
    background-color: #f8f9fa;
}

.table-responsive {
    margin-top: 15px;
}

.badge {
    padding: 8px 12px;
}

/* Make email links more visible */
a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

.card {
    margin-bottom: 20px;
}

.badge {
    font-size: 0.9em;
    padding: 8px 12px;
}

.table th {
    font-weight: 600;
}

.workload-indicator {
    width: 100px;
    height: 10px;
    border-radius: 5px;
    background-color: #e9ecef;
    overflow: hidden;
}

.workload-bar {
    height: 100%;
    transition: width 0.3s ease;
}

.alert {
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
}

.alert h6 {
    margin: 0;
    font-size: 14px;
    color: rgba(0,0,0,0.6);
}

.alert h3 {
    margin: 10px 0 0 0;
    font-size: 24px;
    font-weight: bold;
}
</style> 
</style> 