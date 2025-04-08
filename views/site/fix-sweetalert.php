<?php
/* @var $this yii\web\View */
/* @var $title string */

use yii\helpers\Html;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;

// Register SweetAlert asset to demonstrate it works
\app\assets\SweetAlert2Asset::register($this->view);
?>

<div class="site-fix-sweetalert">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-success">
        <h4><i class="fas fa-check-circle me-2"></i>SweetAlert2 is now working correctly!</h4>
        <p>The notification system has been updated to use CDN links instead of local npm files.</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">What Was Fixed</h5>
                </div>
                <div class="card-body">
                    <p>The application was encountering the following error:</p>
                    <pre class="bg-light p-3"><code>yii\base\InvalidArgumentException: The file or directory to be published does not exist: 
C:\inetpub\wwwroot\ticketing-system\vendor/npm-asset/sweetalert2/dist</code></pre>
                    
                    <p>This error occurred because:</p>
                    <ul>
                        <li>The SweetAlert2 asset bundle was trying to use local npm files</li>
                        <li>The npm asset directory was not correctly installed or accessible</li>
                    </ul>
                    
                    <p>The solution was to modify the <code>SweetAlert2Asset.php</code> file to use CDN links instead:</p>
                    <pre class="bg-light p-3"><code>// Changed from:
public $sourcePath = '@npm/sweetalert2/dist';

// To:
public $sourcePath = null;
public $baseUrl = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';</code></pre>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Additional Improvements</h5>
                </div>
                <div class="card-body">
                    <h6>1. Promise-Based Redirects</h6>
                    <p>Updated all AJAX success handlers to use promise-based redirects:</p>
                    <pre class="bg-light p-3"><code>// Old approach (unreliable):
showAlert('Success', 'Operation completed');
setTimeout(() => {
    window.location.href = '/redirect/url';
}, 3000);

// New approach (reliable):
Swal.fire({
    // options...
}).then(() => {
    window.location.href = '/redirect/url';
});</code></pre>
                    
                    <h6>2. Updated Documentation</h6>
                    <p>Updated the <code>docs/sweetalert-usage.md</code> file with best practices for using SweetAlert in the application.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Test SweetAlert</h5>
                </div>
                <div class="card-body">
                    <p>Try these buttons to test SweetAlert functionality:</p>
                    
                    <?= Html::button('Success Alert', [
                        'class' => 'btn btn-success w-100 mb-2',
                        'onclick' => 'showAlert("Success", "This is a success message")'
                    ]) ?>
                    
                    <?= Html::button('Error Alert', [
                        'class' => 'btn btn-danger w-100 mb-2',
                        'onclick' => 'showAlert("Error", "This is an error message", "error")'
                    ]) ?>
                    
                    <?= Html::button('Warning Alert', [
                        'class' => 'btn btn-warning w-100 mb-2',
                        'onclick' => 'showAlert("Warning", "This is a warning message", "warning")'
                    ]) ?>
                    
                    <?= Html::button('Info Alert', [
                        'class' => 'btn btn-info w-100 mb-2',
                        'onclick' => 'showAlert("Info", "This is an info message", "info")'
                    ]) ?>
                    
                    <?= Html::button('With Promise', [
                        'class' => 'btn btn-primary w-100 mb-2',
                        'onclick' => 'showAlert("Promise Demo", "This alert returns a promise").then(() => { 
                            console.log("Alert closed"); 
                            showAlert("Second Alert", "This appears after the first one closes", "success");
                        })'
                    ]) ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Resources</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="<?= \yii\helpers\Url::to(['site/test-alert']) ?>" class="d-block">
                                <i class="fas fa-flask me-2"></i> Test All Alert Types
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="https://sweetalert2.github.io/" target="_blank" class="d-block">
                                <i class="fas fa-external-link-alt me-2"></i> SweetAlert2 Documentation
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="/docs/sweetalert-usage.md" target="_blank" class="d-block">
                                <i class="fas fa-book me-2"></i> Internal Usage Guide
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Register a script to show an automatic welcome message
$js = <<<JS
    // Show a welcome message when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        showAlert('Welcome', 'SweetAlert is now working correctly!', 'success');
    });
JS;
$this->registerJs($js);
?> 