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
        'modules' => array_filter(array_map('trim', explode(',', $company['module'])))
    ];
}
$companiesJson = Json::encode($companiesForJs);
?>

<div class="create-user">
    <div class="card">
        <div class="card-header">
            <h2><?= Html::encode($this->title) ?></h2>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(['id' => 'create-user-form']); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'company_name')->dropDownList(
                $companyList,
                [
                    'prompt' => 'Select Company',
                    'id' => 'company-dropdown',
                    'class' => 'form-control'
                ]
            ) ?>

            <?= $form->field($model, 'company_email')->textInput([
                'id' => 'company-email',
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

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
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
        
        // Clear previous data
        emailField.val('');
        modulesContainer.empty();
        $('.modules-section').hide();
        
        if (selectedCompany && companies[selectedCompany]) {
            // Set email
            emailField.val(companies[selectedCompany].email);
            
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