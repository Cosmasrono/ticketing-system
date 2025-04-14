<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use app\components\NotificationHelper;

$this->title = 'SweetAlert Test';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-test-alert">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <p>This page demonstrates SweetAlert functionality. You should see:</p>
        <ul>
            <li>A success message from flash (automatically shown when the page loads)</li>
            <li>An info message shown directly (automatically shown when the page loads)</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Test SweetAlert Messages</div>
                <div class="card-body">
                    <p>Click the buttons below to test different SweetAlert messages:</p>

                    <div class="mb-3">
                        <?= Html::button('Success Message', [
                            'class' => 'btn btn-success',
                            'onclick' => 'showAlert("Success", "This is a success message", "success")'
                        ]) ?>
                        
                        <?= Html::button('Error Message', [
                            'class' => 'btn btn-danger',
                            'onclick' => 'showAlert("Error", "This is an error message", "error")'
                        ]) ?>
                        
                        <?= Html::button('Warning Message', [
                            'class' => 'btn btn-warning',
                            'onclick' => 'showAlert("Warning", "This is a warning message", "warning")'
                        ]) ?>
                        
                        <?= Html::button('Info Message', [
                            'class' => 'btn btn-info',
                            'onclick' => 'showAlert("Info", "This is an info message", "info")'
                        ]) ?>
                    </div>
                    
                    <p>Test confirmation dialog:</p>
                    <div class="mb-3">
                        <?= Html::button('Confirmation Dialog', [
                            'class' => 'btn btn-primary',
                            'onclick' => 'confirmAction("Confirm Action", "Are you sure you want to perform this action?", function() { showAlert("Confirmed", "You confirmed the action", "success"); })'
                        ]) ?>
                    </div>
                    
                    <p>Test loading state:</p>
                    <div class="mb-3">
                        <?= Html::button('Show Loading', [
                            'class' => 'btn btn-secondary',
                            'onclick' => 'showLoading("Processing your request..."); setTimeout(function() { hideLoading(); showAlert("Done", "Loading complete", "success"); }, 2000);'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">PHP Notifications</div>
                <div class="card-body">
                    <p>Test notifications from PHP (these will show on the next page load):</p>

                    <div class="mb-3">
                        <?= Html::a('Success Flash', ['test-alert', 'type' => 'success'], [
                            'class' => 'btn btn-success',
                        ]) ?>
                        
                        <?= Html::a('Error Flash', ['test-alert', 'type' => 'error'], [
                            'class' => 'btn btn-danger',
                        ]) ?>
                        
                        <?= Html::a('Warning Flash', ['test-alert', 'type' => 'warning'], [
                            'class' => 'btn btn-warning',
                        ]) ?>
                        
                        <?= Html::a('Info Flash', ['test-alert', 'type' => 'info'], [
                            'class' => 'btn btn-info',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Handle flash message test cases
$type = Yii::$app->request->get('type');
if ($type) {
    $method = $type;
    if (method_exists(NotificationHelper::class, $method)) {
        NotificationHelper::$method("This is a $type message from PHP");
    }
}
?> 