<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\models\ContractRenewal;

/* @var $this yii\web\View */
/* @var $model app\models\ContractRenewal */
/* @var $company app\models\Company */

$this->title = 'Renew Contract - ' . $company->company_name;
$this->params['breadcrumbs'][] = ['label' => 'Profile', 'url' => ['user/profile', 'id' => Yii::$app->user->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contract-renewal">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title mb-0">Contract Renewal Request</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h4>Current Contract Details:</h4>
                <table class="table table-bordered">
                    <tr>
                        <th>Company Name:</th>
                        <td><?= Html::encode($company->company_name) ?></td>
                    </tr>
                    <tr>
                        <th>Contract Start Date:</th>
                        <td><?= $company->start_date ? Yii::$app->formatter->asDate($company->start_date) : 'Not set' ?></td>
                    </tr>
                    <tr>
                        <th>Contract End Date:</th>
                        <td><?= $company->end_date ? Yii::$app->formatter->asDate($company->end_date) : 'Not set' ?></td>
                    </tr>
                </table>
            </div>

            <?php if (!empty($company->end_date)): ?>
                <?php $form = ActiveForm::begin(['id' => 'renewal-form']); ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'extension_period')->dropDownList([
                            3 => '3 Months',
                            6 => '6 Months',
                            12 => '1 Year',
                            24 => '2 Years'
                        ], ['prompt' => 'Select Extension Period']) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'notes')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Enter any additional notes or comments for the renewal request'
                        ]) ?>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <p><strong>Please Note:</strong></p>
                    <ul>
                        <li>Your renewal request will need to be approved by an administrator</li>
                        <li>Current contract terms will remain active until the renewal is approved</li>
                        <li>You will be notified once your request is processed</li>
                    </ul>
                </div>

                <div class="form-group text-center mt-4">
                    <?= Html::submitButton(
                        '<i class="fas fa-paper-plane"></i> Submit Renewal Request',
                        [
                            'class' => 'btn btn-success btn-lg me-2',
                            'data' => ['confirm' => 'Are you sure you want to submit this renewal request?']
                        ]
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-times"></i> Cancel',
                        ['/user/profile', 'id' => Yii::$app->user->id],
                        ['class' => 'btn btn-danger btn-lg']
                    ) ?>
                </div>

                <?php ActiveForm::end(); ?>
            <?php else: ?>
                <div class="alert alert-danger">
                    <p><strong>Contract Information Missing</strong></p>
                    <p>Your company's contract dates are not properly set up in the system. Please contact the administrator to update your contract information with the following details:</p>
                    <ul>
                        <li>Company ID: <?= $company->id ?></li>
                        <li>Company Name: <?= Html::encode($company->company_name) ?></li>
                        <li>Current Contract Status: <?= empty($company->start_date) ? 'No start date' : 'No end date' ?></li>
                    </ul>
                    <?= Html::a(
                        '<i class="fas fa-arrow-left"></i> Back to Profile',
                        ['/user/profile', 'id' => Yii::$app->user->id],
                        ['class' => 'btn btn-primary mt-3']
                    ) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card-header {
    color: #000;
}
.btn-warning {
    color: #000;
}
</style> 