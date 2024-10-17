<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $form yii\widgets\ActiveForm */
/* @var $companyEmail string */

$this->title = 'Create ERP Support Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$issues = [
    'Finance' => [
        'Invoice Error',
        'Payment Not Received',
        'Incorrect Account Balance',
        'Budget Discrepancy',
    ],
    'Sales' => [
        'Order Not Processed',
        'Quotation Error',
        'Sales Report Issue',
        'Customer Data Mismatch',
    ],
    'HR' => [
        'Payroll Issue',
        'Leave Balance Incorrect',
        'Employee Profile Error',
        'Recruitment Module Not Working',
    ],
    'Inventory' => [
        'Stock Level Incorrect',
        'Item Not Found',
        'Warehouse Discrepancy',
        'Reorder Notification Failure',
    ],
    'Production' => [
        'Production Schedule Error',
        'Machine Allocation Issue',
        'Quality Control Report Missing',
        'BOM Error',
    ],
    'IT Support' => [
        'System Not Responding',
        'Access Denied',
        'Application Crash',
        'Data Backup Issue',
    ],
];
?>

<div class="ticket-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to create a support ticket:</p>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->hiddenInput(['value' => Yii::$app->user->id])->label(false) ?>

    <?= $form->field($model, 'company_email')->hiddenInput(['value' => Yii::$app->user->identity->company_email])->label(false) ?>

    <?= $form->field($model, 'module')->dropDownList(
        array_combine(array_keys($issues), array_keys($issues)),
        ['prompt' => 'Select Module', 'id' => 'module']
    ) ?>

    <?= $form->field($model, 'issue')->dropDownList(
        [],
        ['prompt' => 'Select Issue', 'id' => 'issue']
    ) ?>

    <?= $form->field($model, 'description')->textArea(['rows' => 6, 'placeholder' => 'Enter any other pending issues or additional details here']) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs("
    var issues = " . json_encode($issues) . ";
    $('#module').change(function() {
        var module = $(this).val();
        var issueDropdown = $('#issue');
        issueDropdown.empty();
        if (module) {
            var moduleIssues = issues[module] || [];
            issueDropdown.append($('<option>').text('Select an Issue').attr('value', ''));
            $.each(moduleIssues, function(i, issue) {
                issueDropdown.append($('<option>').text(issue).attr('value', issue));
            });
        }
    });

    if ($('#module').val() === 'Finance') {
        $('#module').trigger('change');
    }
");
?>

<style>
/* Orange-themed Ticket Form Styles */
.ticket-create {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #FFF3E0;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.ticket-create h1 {
    color: #FF9800;
    text-align: center;
    margin-bottom: 20px;
}

.ticket-create p {
    color: #F57C00;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border-color: #FFB74D;
}

.form-control:focus {
    border-color: #FF9800;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
}

.btn-primary {
    background-color: #FF9800;
    border-color: #FF9800;
}

.btn-primary:hover, .btn-primary:focus {
    background-color: #F57C00;
    border-color: #F57C00;
}

a {
    color: #FF5722;
}

a:hover {
    color: #E64A19;
}
</style>
