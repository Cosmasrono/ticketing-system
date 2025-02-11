<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use app\models\ContractRenewal;
use app\models\User;
use app\models\Company;
// grid
use yii\grid\GridView;
use yii\data\arrayDataProvider;
// use app\models\ContractRenewal;
use app\assets\SweetAlert2Asset;
use yii\web\View;

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
?>

<style>
/* Fixed Navigation Styles */
.dashboard-nav {
    position: fixed;
    top: 60px; /* Adjust based on your main header height */
    left: 0;
    right: 0;
    width: 100%;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 0 20px;
    z-index: 1000;
}

/* Add padding to content to prevent overlap */
.dashboard-container {
    width: 100%;
    padding-top: 70px; /* Height of nav + some spacing */
}

.nav-trigger {
    display: none;
    padding: 15px 20px;
    background: #2E4374;
    color: white;
    cursor: pointer;
    align-items: center;
    gap: 10px;
}

.nav-links {
    display: flex;
    gap: 20px;
    padding: 15px 0;
    margin: 0;
    list-style: none;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    color: #1a1a1a;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.nav-link:hover, .nav-link.active {
    background: #e0e7ff;
    color: #2E4374;
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
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.client-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.card-header {
    padding: 20px;
    background: #2E4374;
    color: white;
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
    .nav-trigger {
        display: flex;
    }

    .nav-links {
        display: none;
    }

    .nav-links.show {
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
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.client-card {
    width: 100%;
    margin: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

/* Table responsive updates */
.table-responsive {
    width: 100%;
    margin: 0;
    padding: 0;
    overflow-x: auto;
}

.table {
    min-width: 1000px; /* Minimum width to prevent squishing */
    width: 100%;
}

/* Adjust card padding */
.card-body {
    padding: 0; /* Remove default padding */
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

/* Navigation width adjustment */
.dashboard-nav {
    width: 100%;
    max-width: 100%;
    margin: 20px 0;
    padding: 0 20px;
}

.nav-links {
    max-width: 100%;
    justify-content: flex-start;
    overflow-x: auto;
    padding: 15px 0;
}

/* Custom scrollbar for horizontal scroll */
.nav-links::-webkit-scrollbar {
    height: 6px;
}

.nav-links::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
}

.nav-links::-webkit-scrollbar-thumb {
    background: rgba(46, 67, 116, 0.3);
    border-radius: 3px;
}

.nav-links::-webkit-scrollbar-thumb:hover {
    background: rgba(46, 67, 116, 0.5);
}
</style>

<div style="height: 60px;">
    <!-- Spacer for main header -->
</div>

<div class="dashboard-nav">
    <div class="nav-trigger">
        <i class="fas fa-bars"></i> Dashboard Menu
    </div>
    <ul class="nav-links">
        <li><a href="#overview" class="nav-link active" data-section="overview">
            <i class="fas fa-chart-line"></i> <span>Overview</span>
        </a></li>
        <li><a href="#clients" class="nav-link" data-section="clients">
            <i class="fas fa-building"></i> <span>Clients</span>
        </a></li>
        <li><a href="#tickets" class="nav-link" data-section="tickets">
            <i class="fas fa-ticket-alt"></i> <span>Tickets</span>
        </a></li>
        <li><a href="#contracts" class="nav-link" data-section="contracts">
            <i class="fas fa-file-contract"></i> <span>Contracts</span>
        </a></li>
        <li><a href="#users" class="nav-link" data-section="users">
            <i class="fas fa-users"></i> <span>Users</span>
        </a></li>
    </ul>
</div>

<div class="dashboard-container">
    <!-- Users Section -->
    <div id="users" class="content-section bg-white rounded-lg shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold mb-0">
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
                                <th class="px-4 py-3">Name</th>
                                <th class="py-3">Company</th>
                                <th class="py-3">Company Email</th>
                                <th class="py-3">Role</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Created</th>
                                <th class="py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr id="user-<?= $user->id ?>">
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <span class="text-primary fw-bold"><?= strtoupper(substr($user->name, 0, 1)) ?></span>
                                            </div>
                                            <div class="fw-medium"><?= Html::encode($user->name) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= Html::a(
                                            Html::encode($user->company_name),
                                            ['site/profile', 'id' => $user->company_id],
                                            ['class' => 'text-decoration-none text-primary fw-medium']
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
                                            <?= $user->status == 10 ? 'Active' : 'Inactive' ?>
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
    <div id="overview" class="content-section active">
        <h2>Dashboard Overview</h2>
        <!-- Add this section to your admin dashboard -->
       
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
                                        <td><?= Html::encode($ticket->company_name) ?></td>
                                        <td><?= Html::encode($ticket->closed_by) ?></td>
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
<div id="contracts" class="content-section">
    <h2>Contract Renewals</h2>
    <p>Total Renewals: <?= Html::encode(count($contractRenewals)) ?></p>
    <div>
        <?= Html::a('View Renewals', ['contract/index'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?php if (!empty($contractRenewals)): ?>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $contractRenewals,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]),
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                
                [
                    'attribute' => 'requested_by',
                    'label' => 'Company Name',
                    'value' => function ($model) {
                        if (empty($model->requested_by)) {
                            return 'Not Set';
                        }
                        $user = User::findOne($model->requested_by);
                        if (!$user) {
                            return 'User Not Found';
                        }
                        // Get company name
                        $company = Company::findOne($user->company_id);
                        return $company ? Html::encode($company->name) : 'Company Not Found';
                    },
                ],
                [
                    'attribute' => 'renewal_duration',
                    'label' => 'Extension Period',
                    'value' => function ($model) {
                        return $model->renewal_duration . ' Months';
                    },
                ],
                'current_end_date:date',
                'new_end_date:date',
                'notes',
                [
                    'attribute' => 'renewal_status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStatusLabel();
                    }
                ],
                'created_at:datetime',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{approve} {reject}',
                    'buttons' => [
                        'approve' => function ($url, $model) {
                            if ($model->renewal_status === 'approved') {
                                return '<button class="btn btn-success btn-sm" disabled>Approved</button>';
                            }
                            return Html::a('Approve', '#', [
                                'class' => 'btn btn-success btn-sm update-status-btn',
                                'data-id' => $model->id,
                                'data-status' => 'approved',
                            ]);
                        },
                        'reject' => function ($url, $model) {
                            if ($model->renewal_status === 'approved') {
                                return '';  // Hide reject button if approved
                            }
                            return Html::a('Reject', '#', [
                                'class' => 'btn btn-danger btn-sm update-status-btn',
                                'data-id' => $model->id,
                                'data-status' => 'rejected',
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>
    <?php else: ?>
        <div class="alert alert-info">No contract renewals found.</div>
    <?php endif; ?>
</div>

<?php
$updateStatusUrl = Url::to(['site/update-renewal-status']);
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
    $(document).on('click', '.update-status-btn', function(e) {
        e.preventDefault();
        
        var btn = $(this);
        var id = btn.data('id');
        var status = btn.data('status');
        
        if (!confirm('Are you sure you want to ' + status + ' this renewal request?')) {
            return false;
        }
        
        $.ajax({
            url: '$updateStatusUrl',
            type: 'POST',
            data: {
                id: id,
                status: status,
                _csrf: '$csrfToken'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert(response.message);
                    // Reload the page to show updated data
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update status');
                }
            },
            error: function() {
                alert('An error occurred while processing your request');
            }
        });
    });
JS;

$this->registerJs($js);
?>

         




<div id="clients" class="content-section">
    <h2>Clients</h2>
    <!-- Clients content -->


<style>
.client-dashboard {
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.client-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    background: linear-gradient(45deg, #4e73df, #36b9cc);
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

<section id="clients" class="section">
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
                <h3 class="mb-0">Client List</h3>
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
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Email</th>
                                <th>Modules</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
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
                                <td>
                                    <a href="mailto:<?= Html::encode($client->company_email) ?>">
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
                                    $statusDisplay = isset($client->is_active) ? $client->is_active : 
                                                   (isset($client->status_id) ? $client->status_id : 1);
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

    // Navigation functionality
    $('.dash-nav-item').click(function(e) {
        e.preventDefault();
        $('.dash-nav-item').removeClass('active');
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
    // Mobile menu toggle
    document.querySelector('.nav-trigger').addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelector('.nav-links').classList.toggle('show');
    });

    // Handle navigation clicks
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                document.querySelector('.nav-links').classList.remove('show');
            }
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const navHeight = document.querySelector('.dashboard-nav').offsetHeight;
                const headerOffset = 100;
                const elementPosition = targetSection.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
            
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Update active section on scroll
    window.addEventListener('scroll', function() {
        const scrollPosition = window.pageYOffset;
        
        document.querySelectorAll('.section').forEach(section => {
            const sectionTop = section.offsetTop - 150;
            const sectionBottom = sectionTop + section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + section.id) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && !e.target.closest('.dashboard-nav')) {
            document.querySelector('.nav-links').classList.remove('show');
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
            data: { id: userId },
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
 