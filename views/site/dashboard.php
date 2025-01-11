<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;

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

<div class="dashboard-container">

<div class="dashboard-container">
    <h1 class="h3 mb-4 text-gray-800">Help Desk Analytics Dashboard</h1>
    <!-- Summary Cards Row -->
    ...
</div>

    <!-- Summary Cards Row -->
    <!-- <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Active Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalActiveTickets ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Resolution Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $resolutionRate ?? '0%' ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Response Time</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $avgResponseTime ?? '0h' ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingTickets ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>


     <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">User Status Management</h6>
            <span class="badge bg-primary"><?= count($users) ?> Total Users</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= Html::encode($user->name) ?></td>
                                <td>
                                    <span class="badge <?= $user->role === 'developer' ? 'bg-info' : 'bg-primary' ?>">
                                        <?= Html::encode(ucfirst($user->role)) ?>
                                    </span>
                                </td>
                                <td><?= Html::encode($user->company_name) ?></td>
                                <td>
                                    <span class="badge <?= $user->status == 10 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $user->status == 10 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user->status == 10): ?>
                                        <?= Html::button(
                                            '<i class="fas fa-ban"></i> Deactivate',
                                            [
                                                'class' => 'btn btn-sm btn-danger',
                                                'onclick' => "window.toggleUserStatus({$user->id})",
                                                'data-status' => $user->status
                                            ]
                                        ) ?>
                                    <?php else: ?>
                                        <?= Html::button(
                                            '<i class="fas fa-check"></i> Activate',
                                            [
                                                'class' => 'btn btn-sm btn-success',
                                                'onclick' => "window.toggleUserStatus({$user->id})",
                                                'data-status' => $user->status
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Developer Performance Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Developer Performance Metrics</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="devMetricsDropdown" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow">
                    <a class="dropdown-item" href="#">View Details</a>
                    <a class="dropdown-item" href="#">Download Report</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($developerStats as $dev): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card border-left-info h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0"><?= Html::encode($dev['name']) ?></h6>
                                    <span class="badge <?= $dev['active_tickets'] > 5 ? 'bg-danger' : 'bg-success' ?>">
                                        <?= $dev['active_tickets'] ?> Active
                                    </span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <?php
                                    $totalTickets = $dev['completed_tickets'] + $dev['active_tickets'];
                                    $completionPercentage = $totalTickets > 0 
                                        ? ($dev['completed_tickets'] / $totalTickets) * 100 
                                        : 0;
                                    ?>
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?= $completionPercentage ?>%"
                                         aria-valuenow="<?= $completionPercentage ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="small">
                                    <span class="text-success">
                                        <i class="fas fa-check-circle"></i> <?= $dev['completed_tickets'] ?> Resolved
                                    </span>
                                    <span class="float-end text-muted">
                                        <?= $dev['avg_resolution_time'] ?? 'N/A' ?> avg. time
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Analytics Row -->
    <div class="row">
        <!-- Ticket Status Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ticket Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="ticketStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Companies -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Client Support Volume</h6>
                    <small class="text-muted">Total Active Clients: <?= isset($totalCompanies) ? $totalCompanies : 'N/A' ?></small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th class="text-center">Tickets</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topCompanies)): ?>
                                    <?php foreach ($topCompanies as $company): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm">
                                                        <?= substr($company['name'], 0, 2) ?>
                                                    </div>
                                                    <div class="ms-2"><?= Html::encode($company['name']) ?></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">
                                                    <?= number_format($company['ticket_count']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $status = $company['status'] ?? 'active';
                                                $statusClass = [
                                                    'active' => 'success',
                                                    'pending' => 'warning',
                                                    'inactive' => 'danger'
                                                ][$status] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No client data available
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Support Activity</h6>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker <?= getStatusColor($activity['status']) ?>"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">
                                    <a href="<?= Url::to(['ticket/view', 'id' => $activity['ticket_id']]) ?>">
                                        Ticket #<?= Html::encode($activity['ticket_id']) ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?= Yii::$app->formatter->asDatetime($activity['timestamp']) ?>
                                </small>
                            </div>
                            <div class="activity-details">
                                <p class="mb-2">
                                    Status: <span class="badge <?= getStatusColor($activity['status']) ?>">
                                        <?= Html::encode(ucfirst($activity['status'])) ?>
                                    </span>
                                </p>
                                <?php if ($activity['developer']): ?>
                                    <p class="mb-2">
                                        Assigned to: <strong><?= Html::encode($activity['developer']) ?></strong>
                                    </p>
                                <?php endif; ?>
                                <?php if ($activity['escalated_to']): ?>
                                    <p class="mb-2">
                                        Escalated to: <strong><?= Html::encode($activity['escalated_to']) ?></strong>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($activity['escalation_comment'])): ?>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-comment-alt"></i> 
                                        <?= Html::encode($activity['escalation_comment']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- User Status Management Table -->
   

    <!-- Recent Tickets -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Recent Tickets</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTickets as $ticket): ?>
                            <tr>
                                <td><?= $ticket->id ?></td>
                                <td><?= Html::encode($ticket->issue) ?></td>
                                <td><?= Html::encode($ticket->status) ?></td>
                                <td><?= Yii::$app->formatter->asDatetime($ticket->created_at) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



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
    background: #e3e6f0;
    border: 2px solid #fff;
}

.avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e3e6f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
}

.border-left-primary { border-left: 4px solid #4e73df; }
.border-left-success { border-left: 4px solid #1cc88a; }
.border-left-info { border-left: 4px solid #36b9cc; }
.border-left-warning { border-left: 4px solid #f6c23e; }
</style> 