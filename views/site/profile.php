<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\ActiveForm;
use app\models\Ticket;
use app\models\User;
use app\models\Company;

$this->title = 'User Profile: ' . $user->name;

// Get the associated company data
$company = Company::findOne(['company_name' => $user->company_name]);

// Check if company data is found
if ($company === null) {
    throw new \yii\web\NotFoundHttpException('Company not found.');
}

// Calculate total duration
$startDate = new DateTime($company->start_date);
$endDate = new DateTime($company->end_date);
$interval = $startDate->diff($endDate);

// Calculate remaining time
$today = new DateTime();
$remainingTime = $today->diff($endDate);
$isExpired = $today > $endDate;
$daysRemaining = $remainingTime->days;
$showRenewButton = $daysRemaining <= 10 || $isExpired; // Show button if 10 or fewer days remain

// Format the total duration
$duration = '';
if ($interval->y > 0) $duration .= $interval->y . ' years ';
if ($interval->m > 0) $duration .= $interval->m . ' months ';
if ($interval->d > 0) $duration .= $interval->d . ' days';

// Format the remaining time
$remaining = '';
if ($remainingTime->invert) {
    $remaining = '<div class="alert alert-danger">
        <strong>Contract Expired!</strong><br>
        Please renew your contract to continue receiving support.<br>
        ' . ($showRenewButton ? Html::a('<i class="fas fa-sync-alt"></i> Renew Contract', 
            ['/site/renew-contract', 'id' => $company->id], 
            ['class' => 'btn btn-warning mt-2']
        ) : '') . '
    </div>';
} else {
    if ($remainingTime->y > 0) $remaining .= $remainingTime->y . ' years ';
    if ($remainingTime->m > 0) $remaining .= $remainingTime->m . ' months ';
    if ($remainingTime->d > 0) $remaining .= $remainingTime->d . ' days';
    $remaining = '<div>
        <span class="text-success">' . $remaining . ' remaining</span>
        ' . ($showRenewButton ? '<br><br>' . Html::a('<i class="fas fa-sync-alt"></i> Renew Contract', 
            ['/site/renew-contract', 'id' => $company->id], 
            ['class' => 'btn btn-warning']
        ) : '') . '
    </div>';
}

// Fetch the user's role from the users table
$roleName = 'Unknown Role';
if (isset($user->role)) {
    switch ($user->role) {
        case 1:
            $roleName = 'Admin';
            break;
        case 2:
            $roleName = 'User';
            break;
        case 3:
            $roleName = 'Developer';
            break;
        case 4:
            $roleName = 'Super Admin';
            break;
        default:
            $roleName = 'Unknown Role';
            break;
    }
}

// Fetch ticket statistics for the user
$totalTickets = Ticket::find()->where(['created_by' => $user->id])->count();
$totalAssignedTickets = Ticket::find()->where(['assigned_to' => $user->id])->count();
$totalClosedTickets = Ticket::find()->where(['assigned_to' => $user->id, 'status' => 'closed'])->count();
$totalActiveTickets = Ticket::find()->where(['assigned_to' => $user->id, 'status' => '!= closed'])->count();

// Fetch recent tickets
$recentTickets = Ticket::find()
    ->where(['assigned_to' => $user->id])
    ->orderBy(['created_at' => SORT_DESC])
    ->limit(5)
    ->all();
?>

<div class="user-profile">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow">
                <div class="card-header" style="background-color: #FF9800; color: white;">
                    <h1 class="card-title h3 mb-0"><?= Html::encode($this->title) ?></h1>
                </div>
                <div class="card-body">
                    <?php if ($isExpired): ?>
                        <?= $remaining // Show expired message ?>
                    <?php elseif (!empty($warningMessage)): ?>
                        <?= $warningMessage // Show warning message ?>
                    <?php endif; ?>

                    <!-- User Details -->
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">User Information</h4>
                        <?= DetailView::widget([
                            'model' => $user,
                            'attributes' => [
                                'company_name',
                                // 'company_email',
                                [
                                    'label' => 'Role',
                                    'value' => $roleName,
                                    'contentOptions' => ['class' => 'text-primary'],
                                ],
                                [
                                    'label' => 'Contract Status',
                                    'value' => $isExpired ? 'Expired' : 'Active',
                                    'contentOptions' => [
                                        'class' => $isExpired ? 'text-danger' : 'text-success'
                                    ],
                                ],
                            ],
                        ]) ?>
                    </div>

                    <!-- Company Duration -->
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">Company Duration</h4>
                        <?= DetailView::widget([
                            'model' => $company,
                            'attributes' => [
                                'start_date',
                                'end_date',
                                [
                                    'label' => 'Total Duration',
                                    'value' => $duration,
                                    'format' => 'raw',
                                ],
                                [
                                    'label' => 'Time Remaining',
                                    'value' => $remaining,
                                    'format' => 'raw',
                                ],
                            ],
                        ]) ?>
                    </div>

                    <!-- Ticket Statistics Section -->
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">Ticket Statistics</h4>
                        <ul class="list-group">
                            <li class="list-group-item">Total Tickets Created: <?= Html::encode($totalTickets) ?></li>
                            <li class="list-group-item">Total Tickets Assigned: <?= Html::encode($totalAssignedTickets) ?></li>
                            <li class="list-group-item">Total Tickets Closed: <?= Html::encode($totalClosedTickets) ?></li>
                            <li class="list-group-item">Total Active Tickets: <?= Html::encode($totalActiveTickets) ?></li>
                        </ul>
                    </div>

                    <!-- Recent Tickets Section -->
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">Recent Tickets</h4>
                        <div class="list-group">
                            <?php foreach ($recentTickets as $ticket): ?>
                                <div class="list-group-item">
                                    <strong><?= Html::encode($ticket->title) ?></strong><br>
                                    Status: <?= Html::encode($ticket->status) ?><br>
                                    Created At: <?= Html::encode($ticket->created_at) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- User Activity Log Section -->
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">User Activity Log</h4>
                        <ul class="list-group">
                            <!-- Example static log entries; replace with dynamic data as needed -->
                            <li class="list-group-item">Created a ticket on <?= date('Y-m-d H:i:s') ?></li>
                            <li class="list-group-item">Closed a ticket on <?= date('Y-m-d H:i:s') ?></li>
                            <li class="list-group-item">Updated a ticket on <?= date('Y-m-d H:i:s') ?></li>
                        </ul>
                    </div>

                    <!-- Support Information Section -->
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">Support Information</h4>
                        <p>If you need assistance, please contact our support team:</p>
                        <p>Email: support@example.com</p>
                        <p>Phone: +1234567890</p>
                    </div>

                    <!-- Modules Section -->
                    <?php if ($user->modules): ?>
                    <div class="mb-4">
                        <h4 style="color: #FF9800;">Assigned Modules</h4>
                        <div class="list-group">
                            <?php foreach (explode(',', $user->modules) as $module): ?>
                                <div class="list-group-item"><?= Html::encode($module) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 