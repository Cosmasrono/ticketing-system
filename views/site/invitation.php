<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Invitation */

$this->title = 'Send Invitation';
$this->params['breadcrumbs'][] = $this->title;

// Define available modules
$modules = [
    'HR' => 'Human Resources',
    'Power BI' => 'Power BI',
    'Members Portal' => 'Members Portal',
    'Mobile App' => 'Mobile App',
    'Finance' => 'Finance',
    'Credit' => 'Credit',
    'General' => 'General',
    'USSD' => 'USSD',
    'Admin and Security' => 'Admin and Security',
    // Add more modules as needed
];

?>
<div class="invitation-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>
    <!-- company name -->
    <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'company_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'role')->dropDownList([
        'developer' => 'Developer',
        'admin' => 'Admin',
        'user' => 'User',   
    ], ['prompt' => 'Select Role', 'id' => 'role-dropdown']) ?>

    <div id="module-wrapper">
        <?= $form->field($model, 'module')->dropDownList($modules, [
            'prompt' => 'Select Module',
            'id' => 'module-dropdown',
        ]) ?>
    </div>

    <?= Html::hiddenInput('Invitation[module]', 'All', ['id' => 'hidden-module']) ?>

    <div class="form-group">
        <?= Html::submitButton('Send Invitation', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs("
    $(document).ready(function() {
        function toggleModuleVisibility() {
            var selectedRole = $('#role-dropdown').val();
            if (selectedRole === 'admin' || selectedRole === 'developer') {
                $('#module-wrapper').hide();
                $('#hidden-module').prop('disabled', false);
            } else {
                $('#module-wrapper').show();
                $('#hidden-module').prop('disabled', true);
            }
        }

        $('#role-dropdown').change(toggleModuleVisibility);
        toggleModuleVisibility(); // Call on page load
    });
");
?>