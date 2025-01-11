<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $companies app\models\Company[] */

$this->title = 'Create New User';
$this->params['breadcrumbs'][] = $this->title;

// Prepare company list for dropdown
$companyList = ArrayHelper::map($companies, 'company_name', 'company_name');
$companiesJson = [];
foreach ($companies as $company) {
    $companiesJson[$company->company_name] = [
        'name' => $company->name,
        'company_email' => $company->company_email,
        'modules' => is_array($company->modules) ? $company->modules : [],
        'start_date' => $company->start_date,
        'end_date' => $company->end_date
    ];
}
?>

<div class="create-user">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h2 class="text-center mb-0"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'create-user-form']); ?>

                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <?= $form->field($model, 'name')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Enter name',
                                'id' => 'user-name',
                                'required' => true
                            ]) ?>
                        </div>

                        <div class="col-md-12 mb-3">
                            <?= $form->field($model, 'company_name')->dropDownList(
                                $companyList,
                                [
                                    'prompt' => 'Select Company',
                                    'class' => 'form-control',
                                    'id' => 'company-name-select'
                                ]
                            ) ?>
                        </div>

                        <div class="col-md-12 mb-3">
                            <?= $form->field($model, 'company_email')->textInput([
                                'class' => 'form-control',
                                'id' => 'company-email-input'
                            ]) ?>
                        </div>

                        <div id="modules-section" style="display:none;" class="col-12">
                            <h4 class="section-title">Available Modules</h4>
                            <div id="modules-container" class="p-3">
                                <!-- Modules will be populated via JavaScript -->
                            </div>
                        </div>

                        <?= Html::hiddenInput('User[modules]', '', ['id' => 'selected-modules']) ?>

                        <div id="company-details" class="col-12 mt-3" style="display:none;">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Company Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Start Date:</strong> <span id="company-start-date"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>End Date:</strong> <span id="company-end-date"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton('Create User', [
                            'class' => 'btn btn-primary btn-lg px-5',
                            'name' => 'create-user-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$companiesJsonEncoded = json_encode($companiesJson);
$js = <<<JS
    var companiesData = {$companiesJsonEncoded};
    
    // Company selection change handler
    $('#company-name-select').change(function() {
        var selectedCompany = $(this).val();
        var companyData = companiesData[selectedCompany] || {};
        
        console.log("Selected Company: ", selectedCompany);
        console.log("Company Data: ", companyData);
        
        // Auto-fill fields
        $('#user-name').val(companyData.name || '');
        $('#company-email-input').val(companyData.company_email || '');
        $('#user-role').val(companyData.role || '');
        
        // Update company details section
        if (companyData.start_date && companyData.end_date) {
            $('#company-start-date').text(companyData.start_date);
            $('#company-end-date').text(companyData.end_date);
            $('#company-details').slideDown();
        } else {
            $('#company-details').slideUp();
        }
        
        // Update modules section
        var modules = companyData.modules || [];
        var modulesContainer = $('#modules-container');
        modulesContainer.empty();
        
        if (modules.length > 0) {
            modules.forEach(function(module) {
                var checkboxId = 'module-' + module.replace(/\s+/g, '-').toLowerCase();
                modulesContainer.append(
                    '<div class="form-check">' +
                    '<input class="form-check-input module-checkbox" type="checkbox" value="' + module + '" id="' + checkboxId + '">' +
                    '<label class="form-check-label" for="' + checkboxId + '">' + module + '</label>' +
                    '</div>'
                );
            });
            $('#modules-section').show();
        } else {
            $('#modules-section').hide();
        }
        
        updateSelectedModules();
    });

    $(document).on('change', '.module-checkbox', updateSelectedModules);

    function updateSelectedModules() {
        var selectedModules = [];
        $('.module-checkbox:checked').each(function() {
            selectedModules.push($(this).val());
        });
        $('#selected-modules').val(selectedModules.join(','));
    }

    // Form submission handler
    $('#create-user-form').on('submit', function(e) {
        var company = $('#company-name-select').val();
        var email = $('#company-email-input').val();
        
        if (!company) {
            e.preventDefault();
            alert('Please select a company');
            return false;
        }
        
        // Update hidden fields before submission
        updateSelectedModules();
        
        // Debug logging
        console.log('Form submitted with:', {
            company: company,
            email: email,
            modules: $('#selected-modules').val()
        });
    });
JS;
$this->registerJs($js);
?>

<div class="debug-info" style="margin-top: 20px; padding: 10px; background-color: #f0f0f0;">
    <h3>Debug Information</h3>
    <pre><?php print_r(Yii::$app->request->post()); ?></pre>
</div>

