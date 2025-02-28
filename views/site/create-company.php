<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$this->title = 'Create New User';

// Prepare companies for dropdown
$companyList = ArrayHelper::map($clientCompanies, 'company_name', 'company_name');

// Prepare companies data for JavaScript
$companiesForJs = [];
foreach ($clientCompanies as $company) {
    $companiesForJs[$company['company_name']] = [
        'email' => $company['company_email'],
        'name' => $company['name'] ?? str_replace(' Sacco', '', $company['company_name']),
        'modules' => array_filter(array_map('trim', explode(',', $company['module'])))
    ];
}
$companiesJson = Json::encode($companiesForJs);
?>


<style>
        /* Container Styling */
        .container {
        max-width: 100%;
        padding: 0px;
        /* Allow full width */
        /* Add padding for mobile */
    }

    .finefooter{
        padding: 0px 60px;
        margin-bottom: -20px;
        
    }
</style>
<div class="row justify-content-center" style="margin-top: 30px; ">
    <div class=" col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2 class="text-black text-center"><?= Html::encode($this->title) ?></h2>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(['id' => 'create-user-form']); ?>

                
                <?= $form->field($model, 'company_name')->dropDownList(
                    $companyList,
                    [
                        'prompt' => 'Select Company',
                        'id' => 'company-dropdown',
                        'class' => 'form-control'
                    ]
                ) ?>
                <?= $form->field($model, 'name')->textInput([
                    'id' => 'name',
                    'class' => 'form-control',
                    'placeholder' => 'Enter Name',
                    'maxlength' => true,
                    'readonly' => true  // Make it readonly since it will be autofilled
                ]) ?>

                

                <?= $form->field($model, 'company_email')->textInput([
                    'id' => 'company-email',
                    'placeholder' => 'Enter Company Email',
                    'readonly' => true
                ]) ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <?= $form->field($model, 'start_date')->input('date', [
                            'class' => 'form-control',
                            'required' => true,
                            'min' => date('Y-m-d'),
                            'id' => 'start-date-input'
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'end_date')->input('date', [
                            'class' => 'form-control',
                            'required' => true,
                            'min' => date('Y-m-d'),
                            'id' => 'end-date-input'
                        ]) ?>
                    </div>
                </div>

                <div class="modules-section" style="display: none;">
                    <h4>Company Modules</h4>
                    <div id="modules-container" class="row">
                        <!-- Modules will be displayed here -->
                    </div>
                </div>

                <div class="form-group d-flex justify-content-center">
                    <?= Html::submitButton('Save', ['class' => 'btn w-100 p-2 mt-3', 'style' => 'background-color: #EA5626; color:white; max-width: 200px;']) ?>
                </div>


                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
$(document).ready(function() {
    const companies = {$companiesJson};
    
    $('#company-dropdown').change(function() {
        const selectedCompany = $(this).val();
        const modulesContainer = $('#modules-container');
        const emailField = $('#company-email');
        const nameField = $('#name');
        
        // Clear previous data
        emailField.val('');
        nameField.val('');
        modulesContainer.empty();
        $('.modules-section').hide();
        
        if (selectedCompany && companies[selectedCompany]) {
            // Set email and name
            emailField.val(companies[selectedCompany].email);
            nameField.val(companies[selectedCompany].name);
            
            // Display modules
            const modules = companies[selectedCompany].modules;
            if (modules && modules.length > 0) {
                modules.forEach(function(module) {
                    if (module.trim()) {
                        const moduleId = module.replace(/[^a-zA-Z0-9]/g, '_');
                        const moduleHtml = `
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="Company[modules][]" 
                                           value="\${module}" 
                                           id="module-\${moduleId}" 
                                           class="form-check-input"
                                           checked>
                                    <label class="form-check-label" for="module-\${moduleId}">
                                        \${module}
                                    </label>
                                </div>
                            </div>
                        `;
                        modulesContainer.append(moduleHtml);
                    }
                });
                $('.modules-section').show();
            }
        }
    });

    $('#company-end-date').change(function() {
        const startDate = new Date($('#company-start-date').val());
        const endDate = new Date($(this).val());
        
        if (endDate <= startDate) {
            alert('End date must be after start date');
            $(this).val('');
        }
    });
});
JS;

$this->registerJs($js);
?>