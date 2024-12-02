<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\models\SignupForm;

$this->title = 'Create New User';

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
];
?>

<div class="site-create-user">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h2 class="text-center mb-0"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'create-user-form']); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput([
                                'autofocus' => true,
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Enter full name'
                            ]) ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'company_email')->textInput([
                                'type' => 'email',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Enter company email'
                            ]) ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'company_name')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Enter company name'
                            ]) ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'role')->dropDownList([
                                'user' => 'User',
                                'admin' => 'Admin',
                                'developer' => 'Developer'
                            ], [
                                'prompt' => 'Select Role',
                                'id' => 'role-dropdown',
                                'class' => 'form-select form-select-lg'
                            ]) ?>
                        </div>
                    </div>

                    <div id="module-wrapper" class="mt-4">
                        <h4 class="text-muted mb-3">Module Access</h4>
                        <?= $form->field($model, 'selectedModules[]')->checkboxList(
                            $modules,
                            [
                                'item' => function($index, $label, $name, $checked, $value) {
                                    $checked = $checked ? 'checked' : '';
                                    return "
                                        <div class='col-md-4 mb-2'>
                                            <div class='form-check'>
                                                <input class='form-check-input' type='checkbox' name='{$name}' value='{$value}' {$checked}>
                                                <label class='form-check-label'>{$label}</label>
                                            </div>
                                        </div>
                                    ";
                                },
                                'class' => 'row',
                            ]
                        )->label(false) ?>
                    </div>

                    <?= Html::hiddenInput('SignupForm[module]', 'All', ['id' => 'hidden-module']) ?>

                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton('Create User', [
                            'class' => 'btn btn-primary btn-lg px-5',
                            'name' => 'signup-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

        
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    .site-create-user {
        padding: 40px 0;
        background-color: #fff5eb;
        min-height: 100vh;
    }
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: none;
        background-color: #ff7f27 !important;
    }
    .card-body {
        padding: 2rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #ffd4b3;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #ff7f27;
        box-shadow: 0 0 0 0.2rem rgba(255, 127, 39, 0.25);
    }
    .btn-primary {
        background-color: #ff7f27 !important;
        border-color: #ff7f27 !important;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #ff6b00 !important;
        border-color: #ff6b00 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 127, 39, 0.2);
    }
    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.2em;
    }
    .form-check-input:checked {
        background-color: #ff7f27 !important;
        border-color: #ff7f27 !important;
    }
    .form-check-label {
        padding-left: 0.5rem;
        font-size: 1rem;
    }
    .alert {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .alert-success {
        background-color: #ffebda;
        border-color: #ffd4b3;
        color: #cc5500;
    }
    .text-muted {
        color: #cc5500 !important;
    }
    .text-white {
        color: #ffffff !important;
    }
CSS;
$this->registerCss($css);

$js = <<<JS
    $(document).ready(function() {
        function toggleModuleVisibility() {
            var selectedRole = $('#role-dropdown').val();
            if (selectedRole === 'admin' || selectedRole === 'developer') {
                $('#module-wrapper').slideUp();
                $('#hidden-module').prop('disabled', false);
                $('#hidden-module').val('All');
                $('input[name="SignupForm[selectedModules][]"]').prop('checked', false);
            } else {
                $('#module-wrapper').slideDown();
                $('#hidden-module').prop('disabled', true);
                var selectedModules = $('input[name="SignupForm[selectedModules][]"]:checked')
                    .map(function() { return this.value; })
                    .get()
                    .join(',');
                $('#hidden-module').val(selectedModules);
            }
        }

        $('input[name="SignupForm[selectedModules][]"]').change(function() {
            if ($('#role-dropdown').val() === 'user') {
                var selectedModules = $('input[name="SignupForm[selectedModules][]"]:checked')
                    .map(function() { return this.value; })
                    .get()
                    .join(',');
                $('#hidden-module').val(selectedModules);
            }
        });

        $('#role-dropdown').change(toggleModuleVisibility);
        toggleModuleVisibility();
    });
JS;
$this->registerJs($js);
?> 