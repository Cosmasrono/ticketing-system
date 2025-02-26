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

$this->title = $companyDetails['company_name'] . ' Profile';

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

// Get current user's role
$currentUserRole = Yii::$app->user->identity->role;
$isCEO = $currentUserRole === 4;
?>

<div class="company-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => (object)$companyDetails,
        'attributes' => [
            'company_name',
            'company_email:email',
            'role',
            'start_date:date',
            'end_date:date',
            [
                'attribute' => 'status',
                'value' => $companyDetails['status'] ? 'Active' : 'Inactive',
            ],
             
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

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

    <!-- Rest of your view code (tickets and renewals tables) -->
    
    <?php if ($isCEO): ?>
        <!-- CEO Dashboard Section -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">CEO Dashboard Overview</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Overall Statistics -->
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Overall Statistics</h5>
                                <p>Total Tickets: <?= $ticketStats['total'] ?></p>
                                <p>Pending: <?= $ticketStats['pending'] ?></p>
                                <p>High Severity: <?= $ticketStats['high_severity'] ?></p>
                                <p>SLA Breached: <?= $ticketStats['breached_sla'] ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Company Performance -->
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Company Distribution</h5>
                                <?php foreach ($companyStats as $stat): ?>
                                    <p><?= Html::encode($stat['company_name']) ?>: <?= $stat['ticket_count'] ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Developer Performance -->
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Developer Performance</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Developer</th>
                                                <th>Total Tickets</th>
                                                <th>Avg Resolution (hrs)</th>
                                                <th>SLA Breached</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($developerStats as $dev): ?>
                                                <tr>
                                                    <td><?= Html::encode($dev['developer_name']) ?></td>
                                                    <td><?= $dev['total_tickets'] ?></td>
                                                    <td><?= number_format($dev['avg_resolution_time'], 2) ?></td>
                                                    <td><?= $dev['breached_tickets'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing GridView with all tickets -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0">Detailed Ticket Information</h3>
                    </div>
                    <div class="card-body">
                        <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                'allModels' => $tickets,
                                'sort' => [
                                    'attributes' => [
                                        'id',
                                        'title',
                                        'status',
                                        'severity_level',
                                        'created_at',
                                        'last_update_at',
                                        'resolution_deadline',
                                    ],
                                    'defaultOrder' => [
                                        'created_at' => SORT_DESC
                                    ]
                                ],
                                'pagination' => [
                                    'pageSize' => 10,
                                ],
                            ]),
                            'columns' => [
                                'id',
                                'title',
                                [
                                    'attribute' => 'description',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        return Html::tag('div', $model->description, [
                                            'style' => 'max-width: 300px; white-space: normal; word-wrap: break-word;'
                                        ]);
                                    }
                                ],
                                [
                                    'attribute' => 'status',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'breached' => 'danger',
                                        ];
                                        $color = $statusColors[$model->status] ?? 'secondary';
                                        return Html::tag('span', $model->status, ['class' => "badge bg-{$color}"]);
                                    }
                                ],
                                [
                                    'attribute' => 'severity_level',
                                    'value' => function ($model) {
                                        $levels = [
                                            1 => 'Low',
                                            2 => 'Medium',
                                            3 => 'High',
                                            4 => 'Critical'
                                        ];
                                        return $levels[$model->severity_level] ?? 'Unknown';
                                    }
                                ],
                                'module',
                                'issue',
                                [
                                    'attribute' => 'created_at',
                                    'format' => ['datetime']
                                ],
                                [
                                    'attribute' => 'sla_status',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $color = $model->sla_status === 'breached' ? 'danger' : 'success';
                                        return Html::tag('span', $model->sla_status, ['class' => "badge bg-{$color}"]);
                                    }
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view} {update}',
                                    'buttons' => [
                                        'view' => function ($url, $model) {
                                            return Html::a('<i class="fas fa-eye"></i>', ['/ticket/view', 'id' => $model->id], [
                                                'class' => 'btn btn-sm btn-info',
                                                'title' => 'View'
                                            ]);
                                        },
                                        'update' => function ($url, $model) {
                                            return Html::a('<i class="fas fa-edit"></i>', ['/ticket/update', 'id' => $model->id], [
                                                'class' => 'btn btn-sm btn-primary ml-1',
                                                'title' => 'Update'
                                            ]);
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">SLA Compliance</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalTickets = count($tickets);
                        $breachedTickets = count(array_filter($tickets, function($ticket) {
                            return $ticket->sla_status === 'breached';
                        }));
                        $slaCompliance = $totalTickets ? round((($totalTickets - $breachedTickets) / $totalTickets) * 100, 2) : 0;
                        ?>
                        <h2 class="text-center"><?= $slaCompliance ?>%</h2>
                        <p class="text-center">SLA Compliance Rate</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Average Resolution Time</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $avgTime = array_filter(array_column($tickets, 'time_taken'));
                        $averageResolutionTime = count($avgTime) ? round(array_sum($avgTime) / count($avgTime), 2) : 0;
                        ?>
                        <h2 class="text-center"><?= $averageResolutionTime ?> hours</h2>
                        <p class="text-center">Average Resolution Time</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Ticket Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $statusCounts = array_count_values(array_column($tickets, 'status'));
                        ?>
                        <ul class="list-unstyled">
                            <?php foreach ($statusCounts as $status => $count): ?>
                                <li><?= ucfirst($status) ?>: <?= $count ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> 