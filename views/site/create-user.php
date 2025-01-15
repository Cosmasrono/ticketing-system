<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $companies array */

$this->title = 'Create User';

// Define role constants if not in User model
const ROLE_USER = 2;
const ROLE_ADMIN = 1;
const ROLE_DEVELOPER = 3;
const ROLE_SUPER_ADMIN = 4;
?>

<div class="create-user-container">
    <div class="card">
        <div class="card-header">
            <h3>Companies List</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Company Email</th>
                            <th>Modules</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr>
                                <td><?= Html::encode($company->company_name) ?></td>
                                <td><?= Html::encode($company->company_email) ?></td>
                                <td><?= is_array($company->modules) ? Html::encode(implode(', ', $company->modules)) : Html::encode($company->modules) ?></td>
                                <td>
                                    <?php 
                                    echo Html::encode($company->getRoleLabel());
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $existingUser = $company->getUsers()
                                        ->andWhere(['status' => User::STATUS_ACTIVE])
                                        ->one();
                                    
                                    if ($existingUser && Yii::$app->user->isGuest): ?>
                                        <span class="badge badge-warning">Please login to manage users</span>
                                    <?php elseif ($existingUser): ?>
                                        <span class="badge badge-success">User Account Active</span>
                                    <?php else: ?>
                                        <?php $form = ActiveForm::begin(['action' => ['create-user-for-company', 'company_id' => $company->id]]); ?>
                                            <?= Html::hiddenInput('company_email', $company->company_email) ?>
                                            <?= Html::hiddenInput('company_name', $company->company_name) ?>
                                            <?= Html::hiddenInput('modules', is_array($company->modules) ? implode(',', $company->modules) : $company->modules) ?>
                                            <?= Html::submitButton('Create User Account', [
                                                'class' => 'btn btn-primary btn-sm',
                                                'data' => [
                                                    'confirm' => 'This will create a user account and send login credentials to ' . $company->company_email . '. Continue?',
                                                ],
                                            ]) ?>
                                        <?php ActiveForm::end(); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($companies)): ?>
                <div class="alert alert-info">
                    No companies found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.create-user-container {
    padding: 20px;
}

.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fa;
    padding: 15px;
}

.card-header h3 {
    margin: 0;
    color: #333;
}

.table {
    margin-bottom: 0;
}

.btn-primary {
    margin: 2px;
}

.form-inline {
    display: flex;
    align-items: center;
}

.form-control-sm {
    width: 140px;
    margin-right: 10px;
}

td .form-inline {
    justify-content: flex-start;
}

select.form-control-sm {
    height: calc(1.8125rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
}

.badge {
    padding: 0.5em 0.75em;
    font-size: 0.875em;
    border-radius: 0.25rem;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

/* Make disabled buttons more visible */
.btn.disabled, 
.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const roleSelect = this.querySelector('select[name="role"]');
            if (!roleSelect.value) {
                e.preventDefault();
                alert('Please select a role before creating the user account.');
            }
        });
    });
});
</script>