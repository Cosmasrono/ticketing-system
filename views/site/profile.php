<?php
// use Yii;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\ActiveForm;
use app\models\Ticket;
use app\models\User;
use app\models\Company;
use app\models\ContractRenewal;

/* @var $this yii\web\View */
/* @var $company app\models\Company */
/* @var $tickets app\models\Ticket[] */
/* @var $renewals app\models\ContractRenewal[] */

$this->title = $company->company_name . ' Profile';

// Get the associated company data with email
$company = Company::findOne(['company_name' => $user->company_name]);

// Fetch user's company email from users table
$companyEmail = Yii::$app->db->createCommand('
    SELECT company_email 
    FROM users 
    WHERE id = :user_id
')
->bindValue(':user_id', $user->id)
->queryScalar();

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
$roleName = Yii::$app->db->createCommand('
    SELECT role 
    FROM users 
    WHERE id = :user_id
')
->bindValue(':user_id', $user->id)
->queryScalar();

// Debugging line to log the fetched role
Yii::info("Fetched role for user ID {$user->id}: {$roleName}");

// Check if role is empty or null and map the role
switch ($roleName) {
    case 1:
        $roleDisplay = 'Admin';
        break;
    case 2:
        $roleDisplay = 'User';
        break;
    case 3:
        $roleDisplay = 'Developer';
        break;
    case 4:
        $roleDisplay = 'Super Admin';
        break;
    default:
        $roleDisplay = 'Unknown Role';
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

// Check contract expiration and update user status
$today = new DateTime();
$endDate = new DateTime($company->end_date);
$isExpired = $today > $endDate;

if ($isExpired) {
    // Deactivate user if contract is expired
    Yii::$app->db->createCommand()
        ->update('users', ['status' => 0], ['id' => $user->id])
        ->execute();
}

// Fetch current user status
$userStatus = Yii::$app->db->createCommand('
    SELECT status 
    FROM users 
    WHERE id = :user_id
')
->bindValue(':user_id', $user->id)
->queryScalar();

// Fetch tickets and renewals
$tickets = Ticket::find()->where(['company_id' => $company->id])->all();
$renewals = ContractRenewal::find()->where(['company_id' => $company->id])->all();
?>

<div class="company-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $company,
        'attributes' => [
            'name',
            'company_name',
            'company_email:email',
            'start_date:date',
            'end_date:date',
            'role',
            [
                'attribute' => 'status',
                'value' => $company->status ? 'Active' : 'Inactive',
            ],
            'company_type',
            'subscription_level',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <h2>Tickets</h2>
    <?php if (!empty($tickets)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Issue</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?= Html::encode($ticket->issue) ?></td>
                            <td><?= Html::encode($ticket->status) ?></td>
                            <td><?= Yii::$app->formatter->asDatetime($ticket->created_at) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No tickets found.</p>
    <?php endif; ?>

    <h2>Contract Renewals</h2>
    <?php if (!empty($renewals)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Renewal Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($renewals as $renewal): ?>
                        <tr>
                            <td><?= Yii::$app->formatter->asDate($renewal->end_date) ?></td>
                            <td><?= Html::encode($renewal->status) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No renewals found.</p>
    <?php endif; ?>
</div>
