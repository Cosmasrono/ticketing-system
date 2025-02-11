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
?>

<div class="company-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => (object)$companyDetails,
        'attributes' => [
            'company_name',
            'company_email:email',
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
</div> 