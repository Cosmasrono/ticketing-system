<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\ActiveForm;
use app\models\Ticket;
use app\models\User;
use app\models\Company;
use app\models\ContractRenewal;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $company app\models\Company */
/* @var $ticketStats array|null */
/* @var $renewalStats array|null */
/* @var $isSuperAdmin bool */
/* @var $recentTickets array */

$this->title = 'User Profile: ' . Html::encode($user->username);

// Initialize variables
$duration = 'Not available';
$isExpired = false;
$today = new DateTime();
$remainingDays = 0;
$totalDays = 0;
$progressPercentage = 0;

// Calculate duration and status if dates exist
if ($company->start_date && $company->end_date) {
    try {
        $startDate = new DateTime($company->start_date);
        $endDate = new DateTime($company->end_date);
        
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
?>

<div class="user-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Company Information</h2>
    <p><strong>Name:</strong> <?= Html::encode($company->company_name) ?></p>
    <p><strong>Email:</strong> <?= Html::encode($company->company_email) ?></p>
    <p><strong>Status:</strong> <?= Html::encode($company->status) ?></p>
    <p><strong>Start Date:</strong> <?= Html::encode($company->start_date) ?></p>
    <p><strong>End Date:</strong> <?= Html::encode($company->end_date) ?></p>

    <?php if ($isSuperAdmin): ?>
        <h2>Ticket Statistics</h2>
        <p><strong>Total Tickets:</strong> <?= $ticketStats['total'] ?></p>
        <p><strong>Pending Tickets:</strong> <?= $ticketStats['pending'] ?></p>
        <p><strong>Approved Tickets:</strong> <?= $ticketStats['approved'] ?></p>
        <p><strong>Closed Tickets:</strong> <?= $ticketStats['closed'] ?></p>
        <p><strong>Breached SLA:</strong> <?= $ticketStats['breached_sla'] ?></p>

        <h2>Renewal Statistics</h2>
        <p><strong>Total Renewals:</strong> <?= $renewalStats['total'] ?></p>
        <p><strong>Pending Renewals:</strong> <?= $renewalStats['pending'] ?></p>
        <p><strong>Approved Renewals:</strong> <?= $renewalStats['approved'] ?></p>
    <?php endif; ?>

   

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
                            <td><?= Yii::$app->formatter->asDate($company->start_date) ?></td>
                        </tr>
                        <tr>
                            <th>End Date:</th>
                            <td><?= Yii::$app->formatter->asDate($company->end_date) ?></td>
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
                    <?php if ($company->start_date && $company->end_date): ?>
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
                                Yii::debug("Company Details: " . print_r($company, true));
                                ?>
                                
                                <!-- Debug output visible on page -->
                                <div class="alert alert-info">
                                    <small>
                                        Company ID: <?= $company->id ?? 'Not set' ?><br>
                                        Current URL: <?= Yii::$app->request->url ?><br>
                                    </small>
                                </div>

                                <?= Html::a(
                                    '<i class="fas fa-sync-alt"></i> Renew Contract',
                                    ['/site/renew-contract', 'id' => $company->id], // Updated route with leading slash
                                    [
                                        'class' => 'btn btn-warning btn-block',
                                        'data' => [
                                            'method' => 'get',
                                            'params' => ['id' => $company->id]
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

    <!-- Add this after your existing cards -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">Contract Renewal History</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($renewals)): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Requested Date</th>
                                    <th>Current End Date</th>
                                    <th>New End Date</th>
                                    <th>Extension Period</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($renewals as $renewal): ?>
                                    <tr>
                                        <td><?= Yii::$app->formatter->asDate($renewal->created_at) ?></td>
                                        <td><?= Yii::$app->formatter->asDate($renewal->current_end_date) ?></td>
                                        <td><?= Yii::$app->formatter->asDate($renewal->new_end_date) ?></td>
                                        <td><?= $renewal->extension_period ?> months</td>
                                        <td><?= $renewal->getStatusLabel() ?></td>
                                        <td><?= Html::encode($renewal->notes) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No renewal history found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-profile {
    margin: 20px;
}
.user-profile h1 {
    font-size: 24px;
    margin-bottom: 20px;
}
.user-profile h2 {
    font-size: 20px;
    margin-top: 20px;
}
.user-profile p {
    margin: 5px 0;
}
.user-profile ul {
    list-style-type: none;
    padding: 0;
}
.user-profile li {
    margin: 5px 0;
}
</style> 